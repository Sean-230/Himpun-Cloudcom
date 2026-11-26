<?php

namespace App\Support;

/**
 * shortcut: hard-coded preview data shared by the dashboard and the proker form.
 * Replace with Eloquent queries (and seeders) once the models exist.
 */
class DummyData
{
    /** Standing roster: division => [name => jabatan]. 42 active members. */
    private const ROSTER = [
        'HOD' => [
            'Clarissa Tan' => 'Ketua', 'Michelle Wijaya' => 'Wakil Ketua',
            'Darren Lim' => 'Sekretaris', 'Natalia Hendra' => 'Bendahara',
        ],
        'Tech' => [
            'Andreas Gunawan' => 'Koordinator', 'Stefanus Wijaya' => 'Staff', 'Timothy Lee' => 'Staff',
            'Christian Salim' => 'Staff', 'Evelyn Kusnadi' => 'Staff', 'Gabriel Tan' => 'Staff', 'Hans Pangestu' => 'Staff',
        ],
        'PDD Design' => [
            'Clarissa Putri' => 'Koordinator', 'Grace Tanuwijaya' => 'Staff', 'Sherly Antonius' => 'Staff',
            'Vincent Oey' => 'Staff', 'Priscilla Halim' => 'Staff', 'Joshua Lukito' => 'Staff',
        ],
        'PDD Dokum' => [
            'Valerie Susanto' => 'Koordinator', 'Jonathan Kurniawan' => 'Staff', 'Bella Wirawan' => 'Staff',
            'Nathan Setiadi' => 'Staff', 'Cindy Hermawan' => 'Staff', 'Reynaldo Tjandra' => 'Staff',
        ],
        'Event' => [
            'Felicia Hartono' => 'Koordinator', 'Angeline Chandra' => 'Staff', 'Rafael Santoso' => 'Staff',
            'Jessica Lau' => 'Staff', 'Samuel Budiman' => 'Staff', 'Natasha Gozali' => 'Staff', 'Kenneth Alamsyah' => 'Staff',
        ],
        'Social Relation' => [
            'Kevin Pratama' => 'Koordinator', 'Olivia Gunadi' => 'Staff', 'Michael Sutanto' => 'Staff',
            'Stella Rahardja' => 'Staff', 'Aldo Wongso' => 'Staff', 'Melissa Darmawan' => 'Staff',
        ],
        'Properties' => [
            'Bryan Santoso' => 'Koordinator', 'Marcus Halim' => 'Staff', 'Daniel Kurnia' => 'Staff',
            'Livia Santosa' => 'Staff', 'Ricky Tanoto' => 'Staff', 'Shania Widjaja' => 'Staff',
        ],
    ];

    /** Divisions a proker committee can switch on. */
    public static function divisionDefs(): array
    {
        return [
            ['name' => 'Event', 'desc' => 'Rundown, konsep acara, pembicara dan talent'],
            ['name' => 'Inventory', 'desc' => 'Venue, logistik, peralatan dan panggung'],
            ['name' => 'People Relation', 'desc' => 'Humas, izin kampus dan tamu undangan'],
            ['name' => 'Marketing', 'desc' => 'Promosi, penjualan tiket dan sponsor'],
            ['name' => 'PDD Dokum', 'desc' => 'Foto, video dan aftermovie'],
            ['name' => 'PDD Design', 'desc' => 'Logo, poster dan banner'],
            ['name' => 'Tech', 'desc' => 'Landing page, registrasi dan QR check-in'],
        ];
    }

    /** Everyone who can be put in a committee, with how many open prokers they already serve. */
    public static function members(): array
    {
        static $members;
        if ($members) {
            return $members;
        }

        $id = 0;
        foreach (self::ROSTER as $division => $people) {
            foreach ($people as $name => $jabatan) {
                $id++;
                $members[] = [
                    'id' => $id,
                    'name' => $name,
                    'nim' => sprintf('0706012310%03d', $id),
                    'divisi' => $division === 'HOD' ? "HOD · $jabatan" : $division,
                    'jabatan' => $jabatan,
                    'hod' => $division === 'HOD',
                    'roles' => self::openRoles($name),
                ];
            }
        }

        return $members;
    }

    public static function prokers(): array
    {
        static $prokers;
        if ($prokers) {
            return $prokers;
        }

        $d = fn (string $name, array $koor = [], array $anggota = []) => compact('name', 'koor', 'anggota');

        return $prokers = [
            [
                'id' => 1, 'nama' => 'Techfest UC 2026', 'short' => 'Techfest UC', 'jenis' => 'Ulangi', 'status' => 'Berjalan',
                'ketupel' => 'Rafael Santoso', 'pj' => 'Michelle Wijaya', 'hariH' => '2026-12-12', 'lokasi' => 'Auditorium UC',
                'rencana' => 48_500_000, 'realisasi' => 26_850_000,
                'divisions' => [
                    $d('Event', ['Felicia Hartono'], ['Angeline Chandra', 'Samuel Budiman', 'Natasha Gozali', 'Jessica Lau']),
                    $d('Inventory', ['Bryan Santoso'], ['Marcus Halim', 'Daniel Kurnia', 'Ricky Tanoto']),
                    $d('People Relation', ['Kevin Pratama'], ['Olivia Gunadi', 'Michael Sutanto']),
                    $d('Marketing', ['Stella Rahardja'], ['Aldo Wongso', 'Melissa Darmawan']),
                    $d('PDD Dokum', ['Valerie Susanto'], ['Jonathan Kurniawan', 'Bella Wirawan', 'Nathan Setiadi']),
                    $d('PDD Design', ['Clarissa Putri'], ['Grace Tanuwijaya', 'Sherly Antonius']),
                    $d('Tech', ['Andreas Gunawan'], ['Stefanus Wijaya', 'Timothy Lee', 'Christian Salim']),
                ],
            ],
            [
                'id' => 2, 'nama' => 'IMT Orientation Camp', 'short' => 'Orientation Camp', 'jenis' => 'Ulangi', 'status' => 'Pembentukan panitia',
                'ketupel' => 'Kevin Pratama', 'pj' => 'Clarissa Tan', 'hariH' => '2026-12-20', 'lokasi' => 'Pacet, Mojokerto',
                'rencana' => 22_000_000, 'realisasi' => 3_200_000,
                'divisions' => [
                    $d('Event', ['Jessica Lau'], ['Kenneth Alamsyah', 'Natasha Gozali']),
                    $d('Inventory', [], ['Livia Santosa', 'Shania Widjaja']),
                    $d('PDD Dokum', ['Cindy Hermawan']),
                    $d('Marketing'),
                    $d('Tech', ['Evelyn Kusnadi'], ['Andreas Gunawan']),
                ],
            ],
            [
                'id' => 3, 'nama' => 'National Hackathon UC', 'short' => 'Hackathon', 'jenis' => 'Baru', 'status' => 'Usulan',
                'ketupel' => null, 'pj' => 'Darren Lim', 'hariH' => '2027-01-18', 'lokasi' => 'Auditorium UC',
                'rencana' => 35_000_000, 'realisasi' => 0,
                'divisions' => [$d('Event'), $d('Tech'), $d('Marketing')],
            ],
            [
                'id' => 4, 'nama' => 'IMT Class Meeting 2025', 'short' => 'Class Meeting 2025', 'jenis' => 'Ulangi', 'status' => 'Selesai',
                'ketupel' => 'Jessica Lau', 'pj' => 'Natalia Hendra', 'hariH' => '2026-06-14', 'lokasi' => 'Lapangan UC',
                'rencana' => 18_000_000, 'realisasi' => 17_400_000,
                'divisions' => [
                    $d('Event', ['Samuel Budiman'], ['Kenneth Alamsyah']),
                    $d('Inventory', ['Daniel Kurnia'], ['Ricky Tanoto']),
                    $d('PDD Dokum', ['Bella Wirawan'], ['Reynaldo Tjandra']),
                ],
            ],
            [
                'id' => 5, 'nama' => 'Webinar Series: Career Talk', 'short' => 'Webinar Career Talk', 'jenis' => 'Baru', 'status' => 'Berjalan',
                'ketupel' => 'Olivia Gunadi', 'pj' => 'Darren Lim', 'hariH' => '2026-11-28', 'lokasi' => 'Zoom',
                'rencana' => 6_000_000, 'realisasi' => 2_100_000,
                'divisions' => [
                    $d('Event', ['Angeline Chandra'], ['Kenneth Alamsyah']),
                    $d('PDD Design', ['Vincent Oey'], ['Priscilla Halim']),
                    $d('Tech', ['Andreas Gunawan'], ['Hans Pangestu', 'Gabriel Tan']),
                    $d('Marketing', ['Michael Sutanto']),
                ],
            ],
            [
                'id' => 6, 'nama' => 'Alumni Night IMT', 'short' => 'Alumni Night', 'jenis' => 'Ulangi', 'status' => 'LPJ',
                'ketupel' => 'Marcus Halim', 'pj' => 'Natalia Hendra', 'hariH' => '2026-10-24', 'lokasi' => 'Ciputra Hall',
                'rencana' => 15_000_000, 'realisasi' => 14_200_000,
                'divisions' => [
                    $d('Event', ['Natasha Gozali'], ['Samuel Budiman']),
                    $d('Inventory', ['Livia Santosa'], ['Ricky Tanoto']),
                    $d('PDD Dokum', ['Reynaldo Tjandra'], ['Nathan Setiadi']),
                ],
            ],
            [
                'id' => 7, 'nama' => 'IMT Futsal Cup', 'short' => 'Futsal Cup', 'jenis' => 'Baru', 'status' => 'Usulan',
                'ketupel' => null, 'pj' => 'Clarissa Tan', 'hariH' => '2027-02-07', 'lokasi' => 'GOR UC',
                'rencana' => 12_000_000, 'realisasi' => 0,
                'divisions' => [$d('Event'), $d('Inventory')],
            ],
        ];
    }

    /** K, PJ, Ko or A: the person's highest role in one proker, or null. */
    public static function roleIn(array $proker, string $name): ?string
    {
        if ($proker['ketupel'] === $name) {
            return 'K';
        }
        if ($proker['pj'] === $name) {
            return 'PJ';
        }
        foreach ($proker['divisions'] as $division) {
            if (in_array($name, $division['koor'], true)) {
                return 'Ko';
            }
        }
        foreach ($proker['divisions'] as $division) {
            if (in_array($name, $division['anggota'], true)) {
                return 'A';
            }
        }

        return null;
    }

    private static function openRoles(string $name): int
    {
        return count(array_filter(
            self::prokers(),
            fn ($p) => $p['status'] !== 'Selesai' && self::roleIn($p, $name) !== null,
        ));
    }
}
