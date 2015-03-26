<?php

/**
 * пример
 * $x = new SiteMap('/sitemap/index.xml');
 * $x->addUrl('http://site.ru', '0.7');
 */
class SiteMap {

    public $ln = "\n";

    /**
     * максимальное количество URL в одном файле
     * @var int
     */
    public $maxCountUrl = 25000;
    private $_pathFiles;     //папка с файлами sitemap
    private $_urlPath;       //абсолютный урл до папки с xml
    private $_fileIndexPath; //путь к индексному файлу
    private $_f;             //указатель на открытый файл для записи
    private $_ifile = 0;     //счетчи файлов
    private $_i = 0;

    /**
     * 
     * @param string $pathFileIndex путь до индексного файла XML sitemap, обязательно в во вложенной папке а не вместе с index.php
     * @param string $url
     */
    public function __construct($pathFileIndex, $url) {

        $this->_fileIndexPath = $pathFileIndex;
        $this->_urlPath = $url;
        $this->_pathFiles = dirname($pathFileIndex);

        $this->_ifile = 0;
        $this->_i = 0;
    }

    public function addUrl($loc, $priority = null, $changefreq = null, $lastmod = null) {
        if (($this->_i % $this->maxCountUrl) === 0) {
            $this->start();
        }

        fwrite($this->_f, '<url>' . $this->ln);
        fwrite($this->_f, '<loc>' . $loc . '</loc>' . $this->ln);

        if ($lastmod !== null) {
            fwrite($this->_f, '<lastmod>' . date("Y-m-d\TH:i:sP", strtotime($lastmod)) . '</lastmod>' . $this->ln);
        }

        if ($changefreq !== null) {
            fwrite($this->_f, '<changefreq>' . $changefreq . '</changefreq>' . $this->ln);
        }

        if ($priority !== null) {
            fwrite($this->_f, '<priority>' . $priority . '</priority>' . $this->ln);
        }

        fwrite($this->_f, '</url>' . $this->ln);

        $this->_i++;
    }

    public function createSitemap() {
        if ($this->_f !== null) {
            $this->end();
        }

        $f = fopen($this->_fileIndexPath, 'w+');

        fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . $this->ln . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->ln);

        for ($t = 0; $t < $this->_ifile; $t++) {
            fwrite($f, '<sitemap>' . $this->ln);
            fwrite($f, '<loc>' . $this->_urlPath . '/item' . $t . '.xml</loc>' . $this->ln);
            fwrite($f, '</sitemap>' . $this->ln);
        }

        fwrite($f, '</sitemapindex>');
        fclose($f);
    }

    private function getCurrentFile() {
        return $this->_pathFiles . '/item' . $this->_ifile . '.xml';
    }

    private function start() {
        if ($this->_f !== null) {
            $this->end();
        }

        $this->_f = fopen($this->getCurrentFile(), 'w+');
        fwrite($this->_f, '<?xml version="1.0" encoding="UTF-8"?>' . $this->ln . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->ln);
        $this->_ifile++;
    }

    private function end() {
        fwrite($this->_f, '</urlset>');
        fclose($this->_f);
        $this->_f = null;
    }

    public function __destruct() {
        if ($this->_f != null) {
            $this->end();
            $this->createSitemap();
        }
    }

}