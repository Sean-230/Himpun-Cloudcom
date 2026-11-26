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

    /** What each division normally has to deliver: [title, days before Hari-H]. Negative = after Hari-H. */
    private const JOBDESC = [
        'Event' => [['Finalisasi konsep dan master rundown', 30], ['Brief pembicara dan talent', 21], ['Gladi bersih', 3], ['Koordinasi on-site hari-H', 0]],
        'Inventory' => [['Booking venue dan izin penggunaan', 35], ['Daftar kebutuhan alat dan pengadaan', 21], ['Setup panggung dan sound', 1], ['Pembongkaran dan pengembalian alat', -2]],
        'People Relation' => [['Surat izin kampus dan perizinan', 35], ['Undangan tamu dan pembicara', 21], ['Penyambutan tamu hari-H', 0]],
        'Marketing' => [['Rancangan campaign dan target tiket', 30], ['Proposal sponsor terkirim', 28], ['Penjualan tiket dibuka', 21], ['Laporan sponsor dan tiket', -3]],
        'PDD Dokum' => [['Rencana liputan dan shotlist', 14], ['Dokumentasi hari-H (foto dan video)', 0], ['Aftermovie rilis', -14]],
        'PDD Design' => [['Key visual dan logo proker', 35], ['Poster, banner dan feeds', 28], ['Tiket, name tag dan backdrop', 10]],
        'Tech' => [['Landing page dan registrasi', 28], ['Sistem QR check-in', 10], ['Uji coba sistem', 5], ['Rekap data peserta', -2]],
    ];

    /** Default jobdesc of one division as [['title' => ..., 'h' => ...], ...]. */
    public static function jobdescFor(string $division): array
    {
        return array_map(fn ($j) => ['title' => $j[0], 'h' => $j[1]], self::JOBDESC[$division] ?? []);
    }

    /** The four views of the app. */
    public const ROLES = ['hod' => 'HOD', 'pj' => 'PJ', 'koor' => 'Koordinator', 'anggota' => 'Anggota'];

    /** Who the demo switcher can act as, per role. */
    public static function peopleFor(string $role): array
    {
        $members = self::members();
        $letters = fn (string $name) => array_filter(array_map(fn ($p) => self::roleIn($p, $name), self::prokers()));
        $names = array_column($members, 'name');

        return array_values(match ($role) {
            'hod' => array_column(array_filter($members, fn ($m) => $m['hod']), 'name'),
            'pj' => array_unique(array_column(self::prokers(), 'pj')),
            'koor' => array_filter($names, fn ($n) => in_array('Ko', $letters($n), true)),
            // Pure members only: someone who also coordinates somewhere would see more than an anggota.
            'anggota' => array_filter($names, fn ($n) => ($r = $letters($n)) && ! array_diff($r, ['A'])),
            default => [],
        });
    }

    /** Numbers shown on lists and summaries for one proker. */
    public static function stats(array $p): array
    {
        $today = new \DateTimeImmutable('today');
        $hariH = new \DateTimeImmutable($p['hariH']);
        $jobdesc = array_merge([], ...array_map(fn ($d) => $d['jobdesc'], $p['divisions']));
        $sum = fn (string $key) => array_sum(array_map(fn ($d) => count($d[$key]), $p['divisions']));

        return [
            'divisi' => count($p['divisions']),
            'koor' => $sum('koor'),
            'anggota' => $sum('anggota'),
            'total' => count($jobdesc),
            'done' => count(array_filter($jobdesc, fn ($j) => $j['done'])),
            'overdue' => count(array_filter($jobdesc, fn ($j) => ! $j['done'] && $hariH->modify(sprintf('%+d days', -$j['h'])) < $today)),
            'daysLeft' => (int) $today->diff($hariH)->format('%r%a'),
            'spent' => $p['realisasi'] ? (int) round($p['realisasi'] / $p['rencana'] * 100) : null,
        ];
    }

    /** Divisions a proker committee can switch on. */
    public static function divisionDefs(): array
    {
        $defs = [
            ['name' => 'Event', 'desc' => 'Rundown, konsep acara, pembicara dan talent'],
            ['name' => 'Inventory', 'desc' => 'Venue, logistik, peralatan dan panggung'],
            ['name' => 'People Relation', 'desc' => 'Humas, izin kampus dan tamu undangan'],
            ['name' => 'Marketing', 'desc' => 'Promosi, penjualan tiket dan sponsor'],
            ['name' => 'PDD Dokum', 'desc' => 'Foto, video dan aftermovie'],
            ['name' => 'PDD Design', 'desc' => 'Logo, poster dan banner'],
            ['name' => 'Tech', 'desc' => 'Landing page, registrasi dan QR check-in'],
        ];

        return array_map(fn ($d) => $d + ['jobdesc' => self::jobdescFor($d['name'])], $defs);
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

        $list = [
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

        // Weekly meetings per proker: day 0 = Minggu ... 6 = Sabtu.
        $rapat = [
            1 => [['pleno', 4, '19:00', 'Zoom'], ['koor', 1, '20:00', 'Ruang Himpunan']],
            2 => [['pleno', 3, '19:00', 'Zoom']],
            5 => [['pleno', 2, '20:00', 'Zoom']],
        ];
        $today = new \DateTimeImmutable('today');

        foreach ($list as &$p) {
            $hariH = new \DateTimeImmutable($p['hariH']);
            foreach ($p['divisions'] as &$division) {
                $division['jobdesc'] = array_map(function ($j) use ($p, $hariH, $today) {
                    $target = $hariH->modify(sprintf('%+d days', -$j['h']));

                    // Done once the target is a few days behind us (so some stay overdue), or the proker is over.
                    return $j + ['done' => $p['status'] === 'Selesai' || $target < $today->modify('-3 days')];
                }, self::jobdescFor($division['name']));
            }
            unset($division);

            $p['rapat'] = array_map(
                fn ($r, $i) => ['id' => $p['id'] * 10 + $i, 'scope' => $r[0], 'day' => $r[1], 'time' => $r[2], 'place' => $r[3]],
                $rapat[$p['id']] ?? [],
                array_keys($rapat[$p['id']] ?? []),
            );
        }
        unset($p);

        return $prokers = $list;
    }

    public static function proker(int $id): ?array
    {
        foreach (self::prokers() as $p) {
            if ($p['id'] === $id) {
                return $p;
            }
        }

        return null;
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

    /** Dummy data untuk permintaan/rikues antar-koordinator divisi. */
    public static function divisionRequests(): array
    {
        return [
            [
                'id' => 101,
                'proker' => 'Techfest UC 2026',
                'dari_divisi' => 'PDD Dokum',
                'dari_koor' => 'Valerie Susanto',
                'ke_divisi' => 'Inventory',
                'ke_koor' => 'Bryan Santoso',
                'judul' => 'Peminjaman 2 Tripod & Kabel Roll 15m untuk Booth Photobooth',
                'catatan' => 'Dibutuhkan H-1 sore untuk instalasi lighting area depan Auditorium UC.',
                'tenggat' => '2026-12-11',
                'status' => 'Menunggu', // Menunggu, Diproses, Selesai, Ditolak
                'waktu' => '2 jam lalu',
            ],
            [
                'id' => 102,
                'proker' => 'Techfest UC 2026',
                'dari_divisi' => 'Event',
                'dari_koor' => 'Felicia Hartono',
                'ke_divisi' => 'PDD Dokum',
                'ke_koor' => 'Valerie Susanto',
                'judul' => 'Dokumentasi Video Teaser & Bumper Pembukaan Acara',
                'catatan' => 'Durasi maksimal 45 detik, format landscape 16:9 untuk layar utama.',
                'tenggat' => '2026-12-05',
                'status' => 'Diproses',
                'waktu' => '1 hari lalu',
            ],
            [
                'id' => 103,
                'proker' => 'Techfest UC 2026',
                'dari_divisi' => 'Inventory',
                'dari_koor' => 'Bryan Santoso',
                'ke_divisi' => 'PDD Design',
                'ke_koor' => 'Clarissa Putri',
                'judul' => 'Desain Signboard Arah Ruangan & Label Meja Registrasi',
                'catatan' => 'Ukuran A3 siap cetak (PDF CMYK), total 6 titik lokasi.',
                'tenggat' => '2026-12-08',
                'status' => 'Menunggu',
                'waktu' => '3 jam lalu',
            ],
            [
                'id' => 104,
                'proker' => 'Webinar Series: Career Talk',
                'dari_divisi' => 'Event',
                'dari_koor' => 'Angeline Chandra',
                'ke_divisi' => 'Tech',
                'ke_koor' => 'Andreas Gunawan',
                'judul' => 'Setup Link Zoom Webinar & Form Absensi QR Otomatis',
                'catatan' => 'Kapasitas 300 peserta dengan fitur Q&A aktif.',
                'tenggat' => '2026-11-26',
                'status' => 'Selesai',
                'waktu' => '3 hari lalu',
            ],
        ];
    }
}
