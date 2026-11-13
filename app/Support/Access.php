<?php

namespace App\Support;

/**
 * Who may see what. The "current user" is a demo identity kept in the session
 * (role + person) until real login exists.
 *
 * shortcut: session-based demo identity, replace with the authenticated user and policies.
 */
class Access
{
    /** Role and person used when nothing valid is stored in the session. */
    private const DEFAULT = ['hod', 'Michelle Wijaya'];

    public static function me(): array
    {
        $role = session('demo_role');
        $name = session('demo_user');

        if (! isset(DummyData::ROLES[$role]) || ! in_array($name, DummyData::peopleFor($role), true)) {
            [$role, $name] = self::DEFAULT;
        }

        return ['role' => $role, 'name' => $name, 'label' => DummyData::ROLES[$role]];
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::me()['role'], $roles, true);
    }

    /** Route name of the first page each role should land on. */
    public static function home(array $me): string
    {
        return in_array($me['role'], ['hod', 'pj'], true) ? 'dashboard' : 'proker.index';
    }

    /** HOD sees everything, a PJ the running prokers they supervise, koordinator and anggota the ones they work in. */
    public static function canSee(array $me, array $proker): bool
    {
        return match ($me['role']) {
            'hod' => true,
            'pj' => $proker['pj'] === $me['name'] && $proker['status'] === 'Berjalan',
            default => in_array(DummyData::roleIn($proker, $me['name']), ['K', 'Ko', 'A'], true),
        };
    }

    /** What the Proker page lists: the running ones for HOD, otherwise everything the person may open. */
    public static function listFor(array $me): array
    {
        $prokers = DummyData::prokers();

        return array_values($me['role'] === 'hod'
            ? array_filter($prokers, fn ($p) => $p['status'] === 'Berjalan')
            : array_filter($prokers, fn ($p) => self::canSee($me, $p)));
    }

    /** What a person does in one proker, in words: "Koordinator Event", "Anggota Tech", "Ketupel". */
    public static function roleLabel(array $proker, string $name): string
    {
        $in = fn (string $list) => implode(', ', array_column(array_filter($proker['divisions'], fn ($d) => in_array($name, $d[$list], true)), 'name'));

        return match (DummyData::roleIn($proker, $name)) {
            'K' => 'Ketupel',
            'PJ' => 'PJ',
            'Ko' => 'Koordinator '.$in('koor'),
            'A' => 'Anggota '.$in('anggota'),
            default => '–',
        };
    }

    /** Divisions of a proker that this person coordinates. */
    public static function divisionsCoordinated(array $proker, string $name): array
    {
        return array_values(array_column(array_filter($proker['divisions'], fn ($d) => in_array($name, $d['koor'], true)), 'name'));
    }
}
