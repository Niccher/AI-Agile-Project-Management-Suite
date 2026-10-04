<?php

if (!function_exists('is_solo_mode')) {
    /**
     * Determines whether the platform is currently operating in Solo Mode.
     * Solo Mode is true when there is exactly 1 (or 0 during setup) active non-deleted user in the database.
     *
     * @param bool $forceRefresh
     * @return bool
     */
    function is_solo_mode(bool $forceRefresh = false): bool
    {
        static $cachedSoloMode = null;

        if ($cachedSoloMode !== null && !$forceRefresh) {
            return $cachedSoloMode;
        }

        try {
            $db = \Config\Database::connect();
            $count = $db->table('users')
                ->where('deleted_at', null)
                ->where('active', 1)
                ->countAllResults();

            // If 1 or 0 users exist, system operates in Solo Mode
            $cachedSoloMode = ($count <= 1);
        } catch (\Throwable $e) {
            // Fallback safely to solo mode on initial install/unmigrated state
            $cachedSoloMode = true;
        }

        return $cachedSoloMode;
    }
}

if (!function_exists('solo_label')) {
    /**
     * Returns an adaptive label depending on whether the system is in Solo Mode or Team Mode.
     *
     * @param string $teamLabel Label used in multi-user / team context (e.g. "Team Velocity")
     * @param string $soloLabel Label used in single-user context (e.g. "My Velocity")
     * @return string
     */
    function solo_label(string $teamLabel, string $soloLabel): string
    {
        return is_solo_mode() ? $soloLabel : $teamLabel;
    }
}
