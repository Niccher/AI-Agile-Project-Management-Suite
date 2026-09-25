<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\ProjectWikiModel;
use App\Models\ProjectWikiVersionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProjectWikiController extends BaseUserController
{
    protected ProjectModel $projectModel;
    protected ProjectWikiModel $wikiModel;
    protected ProjectWikiVersionModel $versionModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->projectModel = new ProjectModel();
        $this->wikiModel = new ProjectWikiModel();
        $this->versionModel = new ProjectWikiVersionModel();
    }

    /**
     * Wiki / Documentation Reader View
     * GET /projects/wiki/(:segment)
     * GET /projects/wiki/(:segment)/page/(:segment)
     */
    public function index($projectIdentifier, ?string $slug = null)
    {
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $project = $this->projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        if (!$project) {
            throw PageNotFoundException::forPageNotFound('Project not found');
        }

        $projectId = (int)$project['id'];

        // Check if project has any wiki pages; if not, initialize starter doc
        $count = $this->wikiModel->where('project_id', $projectId)->countAllResults();
        if ($count === 0) {
            $this->seedStarterWikiPage($projectId);
        }

        $tree = $this->wikiModel->getTree($projectId);

        // Fetch active page
        $activePage = null;
        if (!empty($slug)) {
            $activePage = $this->wikiModel->where('project_id', $projectId)->where('slug', $slug)->first();
        }

        if (!$activePage) {
            $activePage = $this->wikiModel->where('project_id', $projectId)->orderBy('order_index', 'ASC')->orderBy('id', 'ASC')->first();
        }

        // Fetch author information
        $author = null;
        $updater = null;
        $db = \Config\Database::connect();
        if (!empty($activePage['created_by'])) {
            $author = $db->table('users')->select('username')->where('id', $activePage['created_by'])->get()->getRowArray();
        }
        if (!empty($activePage['updated_by'])) {
            $updater = $db->table('users')->select('username')->where('id', $activePage['updated_by'])->get()->getRowArray();
        }

        return view('user/projects/wiki/index', [
            'project'    => $project,
            'tree'       => $tree,
            'activePage' => $activePage,
            'author'     => $author['username'] ?? 'Team Member',
            'updater'    => $updater['username'] ?? ($author['username'] ?? 'Team Member'),
        ]);
    }

    /**
     * Create new wiki page form
     * GET /projects/wiki/(:segment)/create
     */
    public function create($projectIdentifier)
    {
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $project = $this->projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        if (!$project) {
            throw PageNotFoundException::forPageNotFound('Project not found');
        }

        $projectId = (int)$project['id'];
        $allPages = $this->wikiModel->where('project_id', $projectId)->orderBy('title', 'ASC')->findAll();

        return view('user/projects/wiki/edit', [
            'project'  => $project,
            'page'     => null,
            'allPages' => $allPages,
        ]);
    }

    /**
     * Store new wiki page
     * POST /projects/wiki/(:segment)/store
     */
    public function store($projectIdentifier)
    {
        $isAdmin = auth()->user() && auth()->user()->inGroup('admin', 'manager');
        $project = $this->projectModel->findByIdentifier($projectIdentifier, $this->userId, $isAdmin);
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        $projectId = (int)$project['id'];
        $title = trim($this->request->getPost('title') ?? 'Untitled Page');
        $content = $this->request->getPost('content') ?? '';
        $parentId = (int)($this->request->getPost('parent_id') ?: 0) ?: null;

        $slug = $this->wikiModel->generateSlug($projectId, $title);

        $pageId = $this->wikiModel->insert([
            'project_id'  => $projectId,
            'parent_id'   => $parentId,
            'title'       => $title,
            'slug'        => $slug,
            'content'     => $content,
            'order_index' => 0,
            'version'     => 1,
            'created_by'  => $this->userId,
            'updated_by'  => $this->userId,
        ]);

        // Create version 1 record
        $this->versionModel->insert([
            'page_id'        => $pageId,
            'version_number' => 1,
            'content'        => $content,
            'change_summary' => 'Initial document creation',
            'created_by'     => $this->userId,
        ]);

        $projectTarget = $project['slug'] ?? $projectId;
        return redirect()->to(site_url('projects/wiki/' . $projectTarget . '/page/' . $slug))->with('success', 'Documentation page created successfully.');
    }

    /**
     * Edit wiki page form
     * GET /projects/wiki/page/(:num)/edit
     */
    public function edit(int $pageId)
    {
        $page = $this->wikiModel->find($pageId);
        if (!$page) {
            throw PageNotFoundException::forPageNotFound('Wiki page not found');
        }

        $project = $this->projectModel->find($page['project_id']);
        $allPages = $this->wikiModel->where('project_id', $page['project_id'])
                                   ->where('id !=', $pageId)
                                   ->orderBy('title', 'ASC')
                                   ->findAll();

        return view('user/projects/wiki/edit', [
            'project'  => $project,
            'page'     => $page,
            'allPages' => $allPages,
        ]);
    }

    /**
     * Update wiki page
     * POST /projects/wiki/page/(:num)/update
     */
    public function update(int $pageId)
    {
        $page = $this->wikiModel->find($pageId);
        if (!$page) {
            return redirect()->back()->with('error', 'Wiki page not found.');
        }

        $project = $this->projectModel->find($page['project_id']);
        $title = trim($this->request->getPost('title') ?? $page['title']);
        $content = $this->request->getPost('content') ?? '';
        $summary = trim($this->request->getPost('change_summary') ?? '') ?: 'Updated page content';
        $parentId = (int)($this->request->getPost('parent_id') ?: 0) ?: null;

        $slug = $this->wikiModel->generateSlug($page['project_id'], $title, $pageId);
        $newVersion = (int)$page['version'] + 1;

        // Record version snapshot
        $this->versionModel->insert([
            'page_id'        => $pageId,
            'version_number' => $newVersion,
            'content'        => $content,
            'change_summary' => $summary,
            'created_by'     => $this->userId,
        ]);

        // Update page
        $this->wikiModel->update($pageId, [
            'title'       => $title,
            'slug'        => $slug,
            'parent_id'   => $parentId,
            'content'     => $content,
            'version'     => $newVersion,
            'updated_by'  => $this->userId,
        ]);

        $projectTarget = $project['slug'] ?? $page['project_id'];
        return redirect()->to(site_url('projects/wiki/' . $projectTarget . '/page/' . $slug))->with('success', 'Page updated to version ' . $newVersion . '.');
    }

    /**
     * Delete wiki page
     * POST /projects/wiki/page/(:num)/delete
     */
    public function delete(int $pageId)
    {
        $page = $this->wikiModel->find($pageId);
        if (!$page) {
            return redirect()->back()->with('error', 'Wiki page not found.');
        }

        $project = $this->projectModel->find($page['project_id']);
        $projectId = $page['project_id'];

        // Re-parent subpages if any
        $this->wikiModel->where('parent_id', $pageId)->set(['parent_id' => $page['parent_id']])->update();

        // Delete version history
        $this->versionModel->where('page_id', $pageId)->delete();

        // Delete page
        $this->wikiModel->delete($pageId);

        $projectTarget = $project['slug'] ?? $projectId;
        return redirect()->to(site_url('projects/wiki/' . $projectTarget))->with('success', 'Wiki page deleted.');
    }

    /**
     * Revision History View
     * GET /projects/wiki/page/(:num)/history
     */
    public function history(int $pageId)
    {
        $page = $this->wikiModel->find($pageId);
        if (!$page) {
            throw PageNotFoundException::forPageNotFound('Wiki page not found');
        }

        $project = $this->projectModel->find($page['project_id']);
        $versions = $this->versionModel->getHistory($pageId);

        return view('user/projects/wiki/history', [
            'project'  => $project,
            'page'     => $page,
            'versions' => $versions,
        ]);
    }

    /**
     * Rollback page to a specific historical version
     * POST /projects/wiki/page/(:num)/rollback/(:num)
     */
    public function rollback(int $pageId, int $versionNumber)
    {
        $page = $this->wikiModel->find($pageId);
        if (!$page) {
            return redirect()->back()->with('error', 'Wiki page not found.');
        }

        $targetVersion = $this->versionModel->where('page_id', $pageId)->where('version_number', $versionNumber)->first();
        if (!$targetVersion) {
            return redirect()->back()->with('error', 'Target version revision not found.');
        }

        $newVersion = (int)$page['version'] + 1;
        $summary = 'Rolled back to version ' . $versionNumber;

        // Record snapshot of rollback event
        $this->versionModel->insert([
            'page_id'        => $pageId,
            'version_number' => $newVersion,
            'content'        => $targetVersion['content'],
            'change_summary' => $summary,
            'created_by'     => $this->userId,
        ]);

        // Update active page content
        $this->wikiModel->update($pageId, [
            'content'    => $targetVersion['content'],
            'version'    => $newVersion,
            'updated_by' => $this->userId,
        ]);

        return redirect()->to(site_url('projects/wiki/' . $page['project_id'] . '/page/' . $page['slug']))->with('success', 'Document successfully reverted to version ' . $versionNumber . ' (recorded as version ' . $newVersion . ').');
    }

    /**
     * Seed initial starter documentation page for new projects
     */
    private function seedStarterWikiPage(int $projectId)
    {
        $starterContent = <<<MD
# Project Architecture & Overview

Welcome to the **Knowledge Base & Wiki** for this project!

## 🎯 Overview & Objectives
Document technical specifications, architecture blueprints, API contracts, deployment instructions, and product requirement documents (PRDs) here so your team stays aligned in real time.

---

## 🛠️ Architecture & Tech Stack
- **Framework**: CodeIgniter 4 / PHP 8.2+
- **Database Engine**: MySQL 8.0 with InnoDB Relational Storage
- **Frontend Architecture**: Bootstrap 5 + Hyper SaaS Admin Design System
- **Authentication**: CodeIgniter Shield with Session Management

---

## 🚀 Quick Commands
```bash
# Run database migrations
php spark migrate --all

# Create a local database backup snapshot
php spark db:backup
```

---

## 📋 Team Best Practices
- [x] Every feature branch must be tested locally before PR creation.
- [x] Sprints must have story point estimations on all tickets.
- [x] Log timesheets daily via the integrated Time Tracker.

> [!TIP]
> Click **Edit Page** above to customize this document or add nested sub-pages for specific microservices and guides!
MD;

        $pageId = $this->wikiModel->insert([
            'project_id'  => $projectId,
            'parent_id'   => null,
            'title'       => 'Project Architecture & Overview',
            'slug'        => 'project-architecture-overview',
            'content'     => $starterContent,
            'order_index' => 0,
            'version'     => 1,
            'created_by'  => $this->userId ?: 1,
            'updated_by'  => $this->userId ?: 1,
        ]);

        $this->versionModel->insert([
            'page_id'        => $pageId,
            'version_number' => 1,
            'content'        => $starterContent,
            'change_summary' => 'Initial starter document generated',
            'created_by'     => $this->userId ?: 1,
        ]);
    }
}
