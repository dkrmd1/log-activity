<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin: kelola user, kategori & pengaturan aplikasi
        User::updateOrCreate(
            ['email' => 'admin@bjbsekuritas.co.id'],
            [
                'name' => 'Admin IT & GA',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Group Head: ACC / tolak laporan bulanan
        User::updateOrCreate(
            ['email' => 'gh@bjbsekuritas.co.id'],
            [
                'name' => 'Group Head IT & GA',
                'password' => Hash::make('gh123456'),
                'role' => 'group_head',
            ]
        );

        // Staf: input data & mengajukan laporan bulanan ke Group Head
        User::updateOrCreate(
            ['email' => 'staff@bjbsekuritas.co.id'],
            [
                'name' => 'Staf IT & GA',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
            ]
        );

        // Kategori Isu default (dulunya daftar tetap, sekarang bisa ditambah admin lewat menu "Kelola Kategori")
        foreach (['Sistem/Aplikasi', 'Data/SDI', 'Approval/Reject', 'Jaringan/Infra', 'Lainnya'] as $kategori) {
            Category::firstOrCreate(['type' => 'isu', 'name' => $kategori]);
        }

        if (Issue::count() === 0) {
            Issue::create([
                'occurred_at' => '2026-09-09',
                'category' => 'Jaringan/Infra',
                'title' => 'Gangguan Jaringan JTPM',
                'description' => 'Pada tanggal 09 September 2026 sekitar pukul 14.00 WIB, terjadi gangguan jaringan internet Nusantara/Linknet yang berdampak pada akses operasional sistem di kantor bjb Sekuritas. Gangguan disebabkan oleh adanya pemutusan infrastruktur kabel oleh pihak PUP/Pemkot Bandung sehingga proses recovery membutuhkan waktu lebih lama dari kondisi normal.',
                'requester' => 'Internal Group IT & GA',
                'follow_up' => 'Tim IDXSTI melakukan pengecekan dan menyiapkan alternatif akses melalui VPN untuk keperluan RDP guna mengakses Remote Trading dan BackOffice. Namun, pada pukul 17.59 WIB, router/modem Linknet backup berhasil terkoneksi, sehingga akses dapat kembali menggunakan jaringan backup dan penggunaan VPN tidak diperlukan.',
                'completed_at' => '2026-09-09',
                'status' => 'Done',
            ]);

            Issue::create([
                'occurred_at' => '2026-09-10',
                'category' => 'Jaringan/Infra',
                'title' => 'Gangguan Jaringan JTPM',
                'description' => 'Pada tanggal 10 September 2026 sekitar pukul 14:13 WIB, kembali teridentifikasi gangguan jaringan JTPM yang merupakan kelanjutan dari gangguan pada tanggal 09 September 2026. Gangguan tersebut kembali berdampak terhadap akses operasional sistem di kantor bjb Sekuritas, khususnya akses Remote Desktop (RDP) untuk Remote Trading dan Back Office.',
                'requester' => 'Internal Group IT & GA',
                'follow_up' => 'Sebagai tindak lanjut atas gangguan JTPM pada hari sebelumnya, dilakukan pemantauan dan penggunaan akses alternatif melalui VPN untuk menjaga kelangsungan operasional Remote Trading dan Back Office. Selanjutnya dilakukan pengecekan terhadap router/modem Linknet backup. Pada pukul 18:30 WIB, router/modem Linknet backup berhasil terkoneksi dan dapat digunakan, sehingga akses operasional dapat dilanjutkan melalui jaringan backup dan penggunaan VPN tidak diperlukan.',
                'completed_at' => '2026-10-09',
                'status' => 'Done',
            ]);
        }

        if (ServiceRequest::count() === 0) {
            ServiceRequest::create([
                'occurred_at' => '2026-07-21',
                'category' => 'Akses/Kode Sales',
                'title' => 'Penambahan Kode Sales AN ASKAB',
                'requester' => 'BJB',
                'follow_up' => 'Sudah ditambahkan atas nama Sales ASKAB pada OAO',
                'completed_at' => '2026-06-22',
                'status' => 'Done',
            ]);
        }
    }
}
