<?php
/**
 * algorithm/SAW.php
 * Implementasi murni perhitungan metode Simple Additive Weighting (SAW).
 * Kelas ini HANYA berisi logika matematika, tidak ada logika database atau tampilan.
 */

class SAW
{
    /** @var array Daftar kriteria [['kode','nama','bobot','atribut'], ...] */
    private array $kriteria;

    /** @var array Matriks keputusan [karyawan_id => [kriteria_id => nilai]] */
    private array $matriksKeputusan;

    /** @var array Hasil normalisasi (dihitung saat normalize() dipanggil) */
    private array $matriksNormalisasi = [];

    /** @var array Hasil perangkingan (dihitung saat rank() dipanggil) */
    private array $hasil = [];

    /**
     * @param array $kriteria         Array kriteria dari database
     * @param array $matriksKeputusan Array nilai karyawan per kriteria
     */
    public function __construct(array $kriteria, array $matriksKeputusan)
    {
        $this->kriteria         = $kriteria;
        $this->matriksKeputusan = $matriksKeputusan;
    }

    // ------------------------------------------------------------------
    // LANGKAH 1 – Matriks Keputusan (getter, data sudah dari DB)
    // ------------------------------------------------------------------

    public function getMatriksKeputusan(): array
    {
        return $this->matriksKeputusan;
    }

    // ------------------------------------------------------------------
    // LANGKAH 2 – Normalisasi Matriks
    // Rumus:
    //   Benefit : r_ij = x_ij / max(x_ij)
    //   Cost    : r_ij = min(x_ij) / x_ij
    // ------------------------------------------------------------------

    public function normalize(): self
    {
        // Hitung nilai max dan min untuk setiap kriteria
        $maxPerKriteria = [];
        $minPerKriteria = [];

        foreach ($this->kriteria as $k) {
            $kid = $k['id'];
            $nilaiKolom = array_column(
                array_map(fn($row) => ['v' => $row[$kid] ?? 0], $this->matriksKeputusan),
                'v'
            );

            // Ambil kolom nilai kriteria ini dari semua karyawan
            $kolom = [];
            foreach ($this->matriksKeputusan as $karyawanId => $nilaiRow) {
                $kolom[] = (float)($nilaiRow[$kid] ?? 0);
            }

            $maxPerKriteria[$kid] = max($kolom);
            $minPerKriteria[$kid] = min($kolom);
        }

        // Hitung nilai normalisasi r_ij
        foreach ($this->matriksKeputusan as $karyawanId => $nilaiRow) {
            foreach ($this->kriteria as $k) {
                $kid   = $k['id'];
                $xij   = (float)($nilaiRow[$kid] ?? 0);

                if ($k['atribut'] === 'benefit') {
                    $max = $maxPerKriteria[$kid];
                    $rij = ($max != 0) ? $xij / $max : 0;
                } else { // cost
                    $min = $minPerKriteria[$kid];
                    $rij = ($xij != 0) ? $min / $xij : 0;
                }

                $this->matriksNormalisasi[$karyawanId][$kid] = round($rij, 4);
            }
        }

        return $this; // Untuk method chaining
    }

    public function getMatriksNormalisasi(): array
    {
        return $this->matriksNormalisasi;
    }

    // ------------------------------------------------------------------
    // LANGKAH 3 – Perangkingan (Preferensi)
    // Rumus: V_i = Σ (w_j * r_ij)
    // ------------------------------------------------------------------

    public function rank(): self
    {
        // Buat lookup bobot: [kriteria_id => bobot_desimal]
        $bobotMap = [];
        foreach ($this->kriteria as $k) {
            $bobotMap[$k['id']] = (float)$k['bobot'] / 100; // konversi % ke desimal
        }

        foreach ($this->matriksNormalisasi as $karyawanId => $nilaiRow) {
            $vi = 0.0;
            foreach ($this->kriteria as $k) {
                $kid  = $k['id'];
                $vi  += $bobotMap[$kid] * ($nilaiRow[$kid] ?? 0);
            }
            $this->hasil[$karyawanId] = round($vi, 4);
        }

        // Urutkan dari nilai tertinggi ke terendah
        arsort($this->hasil);

        return $this;
    }

    public function getHasil(): array
    {
        return $this->hasil;
    }

    // ------------------------------------------------------------------
    // Helper – Ringkasan seluruh perhitungan (untuk debugging/laporan)
    // ------------------------------------------------------------------

    public function getRingkasan(): array
    {
        return [
            'matriks_keputusan'    => $this->matriksKeputusan,
            'matriks_normalisasi'  => $this->matriksNormalisasi,
            'nilai_preferensi'     => $this->hasil,
        ];
    }
}