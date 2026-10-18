<?php

namespace App\Helpers;

class QuranHelper
{
    /**
     * Given a Quran page number (1–604), return the current surah name (Arabic)
     * and juz number based on the Madinah Mushaf 604-page layout.
     *
     * Logic: walk the sorted config arrays and keep updating the result for every
     * entry whose starting page is <= the given page. The last match wins, so when
     * multiple surahs share a page we always return the latest one.
     */
    public static function getInfo(int $page): array
    {
        $surahName = '—';
        foreach (config('quran.surahs') as [$startPage, $name]) {
            if ($startPage <= $page) {
                $surahName = $name;
            }
        }

        $juzNumber = 1;
        foreach (config('quran.juzs') as [$startPage, $juz]) {
            if ($startPage <= $page) {
                $juzNumber = $juz;
            }
        }

        return [
            'surah' => $surahName,
            'juz'   => $juzNumber,
        ];
    }
}
