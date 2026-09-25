<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/index', [
            'pageTitle' => 'Chege JIRA — Project & Productivity Tracker',
            'metaDescription' => 'Chege JIRA is a self-hosted project management platform with Kanban boards, time tracking, notes, calendar, and analytics. Docker-ready, open-source.',
            'metaKeywords' => 'project management, kanban, time tracking, productivity, self-hosted, docker, codeigniter',
        ]);
    }

    public function features()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/features', [
            'pageTitle' => 'Features — Chege JIRA',
            'metaDescription' => 'Explore all features of Chege JIRA: project management, Kanban boards, time tracking, calendar, notes, analytics, and more.',
            'metaKeywords' => 'features, project management, kanban, time tracking, analytics',
        ]);
    }

    public function setup()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/setup', [
            'pageTitle' => 'Setup Guide — Chege JIRA',
            'metaDescription' => 'Deploy Chege JIRA with Docker or manually. Step-by-step installation guide with prerequisites, commands, and configuration.',
            'metaKeywords' => 'docker setup, installation, deployment, self-hosted, guide',
        ]);
    }

    public function compare()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/compare', [
            'pageTitle' => 'Compare — ' . esc(setting('App.siteName')),
            'metaDescription' => 'Compare Chege Jira against enterprise SaaS project trackers.',
            'metaKeywords' => 'compare, alternative, project management, vs saas',
        ]);
    }

    public function community()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/community', [
            'pageTitle' => 'Community — ' . esc(setting('App.siteName')),
            'metaDescription' => 'Join the community, contribute code, and report bugs.',
            'metaKeywords' => 'community, github, open source, contribute',
        ]);
    }

    public function pricing()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/pricing', [
            'pageTitle' => 'Pricing — ' . esc(setting('App.siteName')),
            'metaDescription' => 'Chege Jira is 100% free and open-source. Transparent self-hosted pricing with zero hidden fees.',
            'metaKeywords' => 'pricing, free, open source, self-hosted, agile',
        ]);
    }

    public function faqs()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('landing/faqs', [
            'pageTitle' => 'FAQs — Chege JIRA',
            'metaDescription' => 'Frequently asked questions about Chege JIRA: deployment, usage, technical details, and contribution guidelines.',
            'metaKeywords' => 'faq, help, documentation, guide, questions',
        ]);
    }
}
