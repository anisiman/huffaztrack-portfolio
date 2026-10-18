<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Tasmi;

class StudentDashboardController extends Controller
{
    public function index()
    {
        // Find the student record linked to the logged-in user
        $student = Student::where('user_id', auth()->id())->first();

        // Fallback: match by email and auto-link (fixes existing unlinked accounts)
        if (!$student) {
            $student = Student::where('email', auth()->user()->email)->whereNull('user_id')->first();
            if ($student) {
                $student->update(['user_id' => auth()->id()]);
            }
        }

        // If no student record is linked, show dashboard with no data
        if (!$student) {
            return view('student.dashboard', ['student' => null]);
        }

        // ── Attendance Summary ──
        // Scoped to the current academic semester so counts don't carry over from past semesters.
        AcademicPeriod::recalculateAll();
        $activePeriod = AcademicPeriod::current();

        $attendanceSummaryQuery = fn () => Attendance::where('student_id', $student->student_id)
            ->when($activePeriod, fn ($q) => $q->whereBetween('date', [$activePeriod->start_date, $activePeriod->end_date]));

        $totalAttendance   = $attendanceSummaryQuery()->count();
        $presentCount      = $attendanceSummaryQuery()->where('status', 'present')->count();
        $attendancePercent = $totalAttendance > 0
                                 ? round(($presentCount / $totalAttendance) * 100)
                                 : 0;

        // Last 5 attendance records for the Recent Attendance card
        $recentAttendance = Attendance::where('student_id', $student->student_id)
                                      ->orderByDesc('date')
                                      ->take(5)
                                      ->get();

        // ── Hafazan Summary ──
        $totalSessions = Tasmi::where('student_id', $student->student_id)->count();

        // Most recent hafazan session with its page progress
        $latestTasmi = Tasmi::where('student_id', $student->student_id)
                             ->with('progress')
                             ->latest('tasmi_date')
                             ->first();

        // Last 5 hafazan sessions for the Latest Hafazan card
        $recentHafazan = Tasmi::where('student_id', $student->student_id)
                               ->with('progress')
                               ->latest('tasmi_date')
                               ->take(5)
                               ->get();

        $quranPosition = ($latestTasmi && $latestTasmi->progress)
            ? self::quranPosition((int) $latestTasmi->progress->page_reached)
            : null;

        return view('student.dashboard', compact(
            'student',
            'totalAttendance',
            'presentCount',
            'attendancePercent',
            'recentAttendance',
            'totalSessions',
            'latestTasmi',
            'recentHafazan',
            'quranPosition'
        ));
    }

    private static function quranPosition(int $page): array
    {
        // Juz start pages — standard Medina Mushaf (604 pages)
        $juzStarts = [1,22,42,63,83,103,122,142,163,182,202,222,242,262,282,302,322,342,362,382,402,422,442,462,482,502,522,543,563,582];
        $juz = 30;
        foreach ($juzStarts as $i => $start) {
            if ($page < $start) { $juz = $i; break; }
            $juz = $i + 1;
        }

        // Surah name and first page — standard Medina Mushaf
        $surahs = [
            [1,'Al-Fatihah',1],[2,'Al-Baqarah',2],[3,'Ali Imran',50],[4,'An-Nisa\'',77],
            [5,'Al-Ma\'idah',106],[6,'Al-An\'am',128],[7,'Al-A\'raf',151],[8,'Al-Anfal',177],
            [9,'At-Tawbah',187],[10,'Yunus',208],[11,'Hud',221],[12,'Yusuf',235],
            [13,'Ar-Ra\'d',249],[14,'Ibrahim',255],[15,'Al-Hijr',262],[16,'An-Nahl',267],
            [17,'Al-Isra\'',282],[18,'Al-Kahf',293],[19,'Maryam',305],[20,'Ta-Ha',312],
            [21,'Al-Anbiya\'',322],[22,'Al-Hajj',333],[23,'Al-Mu\'minun',342],[24,'An-Nur',350],
            [25,'Al-Furqan',359],[26,'Ash-Shu\'ara\'',367],[27,'An-Naml',377],[28,'Al-Qasas',385],
            [29,'Al-Ankabut',396],[30,'Ar-Rum',404],[31,'Luqman',411],[32,'As-Sajdah',415],
            [33,'Al-Ahzab',418],[34,'Saba\'',428],[35,'Fatir',434],[36,'Ya-Sin',440],
            [37,'As-Saffat',446],[38,'Sad',453],[39,'Az-Zumar',458],[40,'Ghafir',467],
            [41,'Fussilat',477],[42,'Ash-Shura',483],[43,'Az-Zukhruf',489],[44,'Ad-Dukhan',496],
            [45,'Al-Jathiyah',499],[46,'Al-Ahqaf',502],[47,'Muhammad',507],[48,'Al-Fath',511],
            [49,'Al-Hujurat',515],[50,'Qaf',518],[51,'Az-Zariyat',520],[52,'At-Tur',523],
            [53,'An-Najm',526],[54,'Al-Qamar',528],[55,'Ar-Rahman',531],[56,'Al-Waqi\'ah',534],
            [57,'Al-Hadid',537],[58,'Al-Mujadilah',542],[59,'Al-Hashr',545],[60,'Al-Mumtahanah',549],
            [61,'As-Saf',551],[62,'Al-Jumu\'ah',553],[63,'Al-Munafiqun',554],[64,'At-Taghabun',556],
            [65,'At-Talaq',558],[66,'At-Tahrim',560],[67,'Al-Mulk',562],[68,'Al-Qalam',564],
            [69,'Al-Haqqah',566],[70,'Al-Ma\'arij',568],[71,'Nuh',570],[72,'Al-Jinn',572],
            [73,'Al-Muzzammil',574],[74,'Al-Muddathir',575],[75,'Al-Qiyamah',577],[76,'Al-Insan',578],
            [77,'Al-Mursalat',580],[78,'An-Naba\'',582],[79,'An-Nazi\'at',583],[80,'\'Abasa',585],
            [81,'At-Takwir',586],[82,'Al-Infitar',587],[83,'Al-Mutaffifin',587],[84,'Al-Inshiqaq',589],
            [85,'Al-Buruj',590],[86,'At-Tariq',591],[87,'Al-A\'la',591],[88,'Al-Ghashiyah',592],
            [89,'Al-Fajr',593],[90,'Al-Balad',594],[91,'Ash-Shams',595],[92,'Al-Layl',595],
            [93,'Ad-Duha',596],[94,'Ash-Sharh',596],[95,'At-Tin',597],[96,'Al-\'Alaq',597],
            [97,'Al-Qadr',598],[98,'Al-Bayyinah',598],[99,'Az-Zalzalah',599],[100,'Al-\'Adiyat',599],
            [101,'Al-Qari\'ah',600],[102,'At-Takathur',600],[103,'Al-\'Asr',601],[104,'Al-Humazah',601],
            [105,'Al-Fil',601],[106,'Quraysh',602],[107,'Al-Ma\'un',602],[108,'Al-Kawthar',602],
            [109,'Al-Kafirun',603],[110,'An-Nasr',603],[111,'Al-Masad',603],
            [112,'Al-Ikhlas',604],[113,'Al-Falaq',604],[114,'An-Nas',604],
        ];

        $surahNum  = 1;
        $surahName = 'Al-Fatihah';
        foreach ($surahs as [$num, $name, $startPage]) {
            if ($startPage <= $page) { $surahNum = $num; $surahName = $name; }
            else break;
        }

        return ['juz' => $juz, 'surah_number' => $surahNum, 'surah_name' => $surahName];
    }
}
