<?php
include "koneksi.php";

// Fungsi untuk mengambil mode absensi
function getModeAbsen($mode_absen)
{
    switch ($mode_absen) {
        case 1:
            return "Masuk";
        case 2:
            return "Istirahat";
        case 3:
            return "Kembali";
        case 4:
            return "Pulang";
        default:
            return "Tidak Diketahui";
    }
}

// Baca tabel status untuk mode absensi
$sql = $konek->query("SELECT * FROM status");
if (!$sql) {
    die("Query gagal: " . $konek->error);
}
$data = $sql->fetch_assoc();
$mode_absen = $data['mode'] ?? 0; // Default ke 0 jika tidak ada data
$mode = getModeAbsen($mode_absen);

// Baca tabel tmprfid
$baca_kartu = $konek->query("SELECT * FROM tmprfid");
if (!$baca_kartu) {
    die("Query gagal: " . $konek->error);
}
$data_kartu = $baca_kartu->fetch_assoc();
$nokartu = $data_kartu['nokartu'] ?? "";
?>

<div class="container-fluid" style="text-align: center;">
    <?php if ($nokartu == "") { ?>
        <br> <h3>Absen : <?= htmlspecialchars($mode); ?></h3>
        <h3>Silahkan Tempelkan Kartu RFID Anda</h3>
        <img src="images/rfid.png" style="width: 200px"> <br>
        <br> <img src="images/animasi2.gif">
    <?php } else {
        // Cek nomor kartu di tabel karyawan
        $stmt = $konek->prepare("SELECT * FROM siswa WHERE nokartu = ?");
        if (!$stmt) {
            die("Query gagal: " . $konek->error);
        }
        $stmt->bind_param("s", $nokartu);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo "<h1>Maaf! Kartu Tidak Dikenali</h1>";
        } else {
            $data_karyawan = $result->fetch_assoc();
            $nama = htmlspecialchars($data_karyawan['nama']);

            // Tanggal dan jam sekarang
            date_default_timezone_set('Asia/Jakarta');
            $tanggal = date('Y-m-d');
            $jam = date('H:i:s');

            // Cek absensi pada tanggal yang sama
            $stmt = $konek->prepare("SELECT * FROM absensi WHERE nokartu = ? AND tanggal = ?");
            if (!$stmt) {
                die("Query gagal: " . $konek->error);
            }
            $stmt->bind_param("ss", $nokartu, $tanggal);
            $stmt->execute();
            $result_absen = $stmt->get_result();

            if ($result_absen->num_rows == 0) {
                echo "<h1>Selamat Datang <br> $nama</h1>";
                $stmt = $konek->prepare("INSERT INTO absensi (nokartu, tanggal, jam_masuk) VALUES (?, ?, ?)");
                if (!$stmt) {
                    die("Query gagal: " . $konek->error);
                }
                $stmt->bind_param("sss", $nokartu, $tanggal, $jam);
                $stmt->execute();
            } else {
                // Update absensi berdasarkan mode
                switch ($mode_absen) {
                    case 2:
                        echo "<h1>Selamat Istirahat <br> $nama</h1>";
                        $stmt = $konek->prepare("UPDATE absensi SET jam_istirahat = ? WHERE nokartu = ? AND tanggal = ?");
                        break;
                    case 3:
                        echo "<h1>Selamat Datang Kembali <br> $nama</h1>";
                        $stmt = $konek->prepare("UPDATE absensi SET jam_kembali = ? WHERE nokartu = ? AND tanggal = ?");
                        break;
                    case 4:
                        echo "<h1>Selamat Jalan <br> $nama</h1>";
                        $stmt = $konek->prepare("UPDATE absensi SET jam_pulang = ? WHERE nokartu = ? AND tanggal = ?");
                        break;
                    default:
                        echo "<h1>Mode Absen Tidak Valid</h1>";
                        $stmt = null;
                        break;
                }
                if (isset($stmt)) {
                    $stmt->bind_param("sss", $jam, $nokartu, $tanggal);
                    $stmt->execute();
                }
            }
        }

        // Kosongkan tabel tmprfid
        if (!$konek->query("DELETE FROM tmprfid")) {
            die("Query gagal: " . $konek->error);
        }
    } ?>
</div>
