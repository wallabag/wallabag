<?php
/**
 * Class to create and manage a Zip file.
 *
 * Inspired by CreateZipFile by Rochak Chauhan  www.rochakchauhan.com (http://www.phpclasses.org/browse/package/2322.html)
 * and
 * http://www.pkware.com/documents/casestudies/APPNOTE.TXT Zip file specification.
 *
 * License: GNU LGPL, Attribution required for commercial implementations, requested for everything else.
 *
 * @author A. Grandt <php@grandt.com>
 * @copyright 2009-2013 A. Grandt
 * @license GNU LGPL, Attribution required for commercial implementations, requested for everything else.
 * @link http://www.phpclasses.org/package/6110
 * @link https://github.com/Grandt/PHPZip
 * @version 1.38
 */
class Zip {
    const VERSION = 1.38;

    const ZIP_LOCAL_FILE_HEADER = "\x50\x4b\x03\x04"; // Local file header signature
    const ZIP_CENTRAL_FILE_HEADER = "\x50\x4b\x01\x02"; // Central file header signature
    const ZIP_END_OF_CENTRAL_DIRECTORY = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record

    const EXT_FILE_ATTR_DIR = "\x10\x00\xFF\x41";
    const EXT_FILE_ATTR_FILE = "\x00\x00\xFF\x81";

    const ATTR_VERSION_TO_EXTRACT = "\x14\x00"; // Version needed to extract
    const ATTR_MADE_BY_VERSION = "\x1E\x03"; // Made By Version

    private $zipMemoryThreshold = 1048576; // Autocreate tempfile if the zip data exceeds 1048576 bytes (1 MB)

    private $zipData = NULL;
    private $zipFile = NULL;
    private $zipComment = NULL;
    private $cdRec = array(); // central directory
    private $offset = 0;
    private $isFinalized = FALSE;
    private $addExtraField = TRUE;

    private $streamChunkSize = 65536;
    private $streamFilePath = NULL;
    private $streamTimeStamp = NULL;
    private $streamComment = NULL;
    private $streamFile = NULL;
    private $streamData = NULL;
    private $streamFileLength = 0;

    /**
     * Constructor.
     *
     * @param boolean $useZipFile Write temp zip data to tempFile? Default FALSE
     */
    function __construct($useZipFile = FALSE) {
        if ($useZipFile) {
            $this->zipFile = tmpfile();
        } else {
            $this->zipData = "";
        }
    }

    function __destruct() {
        if (is_resource($this->zipFile)) {
            fclose($this->zipFile);
        }
        $this->zipData = NULL;
    }

    /**
     * Extra fields on the Zip directory records are Unix time codes needed for compatibility on the default Mac zip archive tool.
     * These are enabled as default, as they do no harm elsewhere and only add 26 bytes per file added.
     *
     * @param bool $setExtraField TRUE (default) will enable adding of extra fields, anything else will disable it.
     */
    function setExtraField($setExtraField = TRUE) {
        $this->addExtraField = ($setExtraField === TRUE);
    }

    /**
     * Set Zip archive comment.
     *
     * @param String $newComment New comment. NULL to clear.
     * @return bool $success
     */
    public function setComment($newComment = NULL) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->zipComment = $newComment;

        return TRUE;
    }

    /**
     * Set zip file to write zip data to.
     * This will cause all present and future data written to this class to be written to this file.
     * This can be used at any time, even after the Zip Archive have been finalized. Any previous file will be closed.
     * Warning: If the given file already exists, it will be overwritten.
     *
     * @param String $fileName
     * @return bool $success
     */
    public function setZipFile($fileName) {
        if (is_file($fileName)) {
            unlink($fileName);
        }
        $fd=fopen($fileName, "x+b");
        if (is_resource($this->zipFile)) {
            rewind($this->zipFile);
            while (!feof($this->zipFile)) {
                fwrite($fd, fread($this->zipFile, $this->streamChunkSize));
            }

            fclose($this->zipFile);
        } else {
            fwrite($fd, $this->zipData);
            $this->zipData = NULL;
        }
        $this->zipFile = $fd;

        return TRUE;
    }

    /**
     * Add an empty directory entry to the zip archive.
     * Basically this is only used if an empty directory is added.
     *
     * @param String $directoryPath Directory Path and name to be added to the archive.
     * @param int    $timestamp     (Optional) Timestamp for the added directory, if omitted or set to 0, the current time will be used.
     * @param String $fileComment   (Optional) Comment to be added to the archive for this directory. To use fileComment, timestamp must be given.
     * @return bool $success
     */
    public function addDirectory($directoryPath, $timestamp = 0, $fileComment = NULL) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $directoryPath = str_replace("\\", "/", $directoryPath);
        $directoryPath = rtrim($directoryPath, "/");

        if (strlen($directoryPath) > 0) {
            $this->buildZipEntry($directoryPath.'/', $fileComment, "\x00\x00", "\x00\x00", $timestamp, "\x00\x00\x00\x00", 0, 0, self::EXT_FILE_ATTR_DIR);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Add a file to the archive at the specified location and file name.
     *
     * @param String $data        File data.
     * @param String $filePath    Filepath and name to be used in the archive.
     * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.
     * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.
     * @param bool   $compress    (Optional) Compress file, if set to FALSE the file will only be stored. Default TRUE.
     * @return bool $success
     */
    public function addFile($data, $filePath, $timestamp = 0, $fileComment = NULL, $compress = TRUE) {
        if ($this->isFinalized) {
            return FALSE;
        }

        if (is_resource($data) && get_resource_type($data) == "stream") {
            $this->addLargeFile($data, $filePath, $timestamp, $fileComment);
            return FALSE;
        }

        $gzData = "";
        $gzType = "\x08\x00"; // Compression type 8 = deflate
        $gpFlags = "\x00\x00"; // General Purpose bit flags for compression type 8 it is: 0=Normal, 1=Maximum, 2=Fast, 3=super fast compression.
        $dataLength = strlen($data);
        $fileCRC32 = pack("V", crc32($data));

        if ($compress) {
            $gzTmp = gzcompress($data);
            $gzData = substr(substr($gzTmp, 0, strlen($gzTmp) - 4), 2); // gzcompress adds a 2 byte header and 4 byte CRC we can't use.
            // The 2 byte header does contain useful data, though in this case the 2 parameters we'd be interrested in will always be 8 for compression type, and 2 for General purpose flag.
            $gzLength = strlen($gzData);
        } else {
            $gzLength = $dataLength;
        }

        if ($gzLength >= $dataLength) {
            $gzLength = $dataLength;
            $gzData = $data;
            $gzType = "\x00\x00"; // Compression type 0 = stored
            $gpFlags = "\x00\x00"; // Compression type 0 = stored
        }

        if (!is_resource($this->zipFile) && ($this->offset + $gzLength) > $this->zipMemoryThreshold) {
            $this->zipflush();
        }

        $this->buildZipEntry($filePath, $fileComment, $gpFlags, $gzType, $timestamp, $fileCRC32, $gzLength, $dataLength, self::EXT_FILE_ATTR_FILE);

        $this->zipwrite($gzData);

        return TRUE;
    }

    /**
     * Add the content to a directory.
     *
     * @author Adam Schmalhofer <Adam.Schmalhofer@gmx.de>
     * @author A. Grandt
     *
     * @param String $realPath       Path on the file system.
     * @param String $zipPath        Filepath and name to be used in the archive.
     * @param bool   $recursive      Add content recursively, default is TRUE.
     * @param bool   $followSymlinks Follow and add symbolic links, if they are accessible, default is TRUE.
     * @param array &$addedFiles     Reference to the added files, this is used to prevent duplicates, efault is an empty array.
     *                               If you start the function by parsing an array, the array will be populated with the realPath
     *                               and zipPath kay/value pairs added to the archive by the function.
     */
    public function addDirectoryContent($realPath, $zipPath, $recursive = TRUE, $followSymlinks = TRUE, &$addedFiles = array()) {
        if (file_exists($realPath) && !isset($addedFiles[realpath($realPath)])) {
            if (is_dir($realPath)) {
                $this->addDirectory($zipPath);
            }

            $addedFiles[realpath($realPath)] = $zipPath;

            $iter = new DirectoryIterator($realPath);
            foreach ($iter as $file) {
                if ($file->isDot()) {
                    continue;
                }
                $newRealPath = $file->getPathname();
                $newZipPath = self::pathJoin($zipPath, $file->getFilename());

                if (file_exists($newRealPath) && ($followSymlinks === TRUE || !is_link($newRealPath))) {
                    if ($file->isFile()) {
                        $addedFiles[realpath($newRealPath)] = $newZipPath;
                        $this->addLargeFile($newRealPath, $newZipPath);
                    } else if ($recursive === TRUE) {
                        $this->addDirectoryContent($newRealPath, $newZipPath, $recursive);
                    } else {
                        $this->addDirectory($zipPath);
                    }
                }
            }
        }
    }

    /**
     * Add a file to the archive at the specified location and file name.
     *
     * @param String $dataFile    File name/path.
     * @param String $filePath    Filepath and name to be used in the archive.
     * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.
     * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.
     * @return bool $success
     */
    public function addLargeFile($dataFile, $filePath, $timestamp = 0, $fileComment = NULL)   {
        if ($this->isFinalized) {
            return FALSE;
        }

        if (is_string($dataFile) && is_file($dataFile)) {
            $this->processFile($dataFile, $filePath, $timestamp, $fileComment);
        } else if (is_resource($dataFile) && get_resource_type($dataFile) == "stream") {
            $fh = $dataFile;
            $this->openStream($filePath, $timestamp, $fileComment);

            while (!feof($fh)) {
                $this->addStreamData(fread($fh, $this->streamChunkSize));
            }
            $this->closeStream($this->addExtraField);
        }
        return TRUE;
    }

    /**
     * Create a stream to be used for large entries.
     *
     * @param String $filePath    Filepath and name to be used in the archive.
     * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.
     * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.
     * @return bool $success
     */
    public function openStream($filePath, $timestamp = 0, $fileComment = null)   {
        if (!function_exists('sys_get_temp_dir')) {
            die ("ERROR: Zip " . self::VERSION . " requires PHP version 5.2.1 or above if large files are used.");
        }

        if ($this->isFinalized) {
            return FALSE;
        }

        $this->zipflush();

        if (strlen($this->streamFilePath) > 0) {
            closeStream();
        }

        $this->streamFile = tempnam(sys_get_temp_dir(), 'Zip');
        $this->streamData = fopen($this->streamFile, "wb");
        $this->streamFilePath = $filePath;
        $this->streamTimestamp = $timestamp;
        $this->streamFileComment = $fileComment;
        $this->streamFileLength = 0;

        return TRUE;
    }
    /**
     * Add data to the open stream.
     *
     * @param String $data
     * @return $length bytes added or FALSE if the archive is finalized or there are no open stream.
     */
    public function addStreamData($data) {
        if ($this->isFinalized || strlen($this->streamFilePath) == 0) {
            return FALSE;
        }

        $length = fwrite($this->streamData, $data, strlen($data));
        if ($length != strlen($data)) {
            die ("<p>Length mismatch</p>\n");
        }
        $this->streamFileLength += $length;

        return $length;
    }

    /**
     * Close the current stream.
     *
     * @return bool $success
     */
    public function closeStream() {
        if ($this->isFinalized || strlen($this->streamFilePath) == 0) {
            return FALSE;
        }

        fflush($this->streamData);
        fclose($this->streamData);

        $this->processFile($this->streamFile, $this->streamFilePath, $this->streamTimestamp, $this->streamFileComment);

        $this->streamData = null;
        $this->streamFilePath = null;
        $this->streamTimestamp = null;
        $this->streamFileComment = null;
        $this->streamFileLength = 0;

        // Windows is a little slow at times, so a millisecond later, we can unlink this.
        unlink($this->streamFile);

        $this->streamFile = null;

        return TRUE;
    }

    private function processFile($dataFile, $filePath, $timestamp = 0, $fileComment = null) {
        if ($this->isFinalized) {
            return FALSE;
        }

        $tempzip = tempnam(sys_get_temp_dir(), 'ZipStream');

        $zip = new ZipArchive;
        if ($zip->open($tempzip) === TRUE) {
            $zip->addFile($dataFile, 'file');
            $zip->close();
        }

        $file_handle = fopen($tempzip, "rb");
        $stats = fstat($file_handle);
        $eof = $stats['size']-72;

        fseek($file_handle, 6);

        $gpFlags = fread($file_handle, 2);
        $gzType = fread($file_handle, 2);
        fread($file_handle, 4);
        $fileCRC32 = fread($file_handle, 4);
        $v = unpack("Vval", fread($file_handle, 4));
        $gzLength = $v['val'];
        $v = unpack("Vval", fread($file_handle, 4));
        $dataLength = $v['val'];

        $this->buildZipEntry($filePath, $fileComment, $gpFlags, $gzType, $timestamp, $fileCRC32, $gzLength, $dataLength, self::EXT_FILE_ATTR_FILE);

        fseek($file_handle, 34);
        $pos = 34;

        while (!feof($file_handle) && $pos < $eof) {
            $datalen = $this->streamChunkSize;
            if ($pos + $this->streamChunkSize > $eof) {
                $datalen = $eof-$pos;
            }
            $data = fread($file_handle, $datalen);
            $pos += $datalen;

            $this->zipwrite($data);
            
            flush();
        }

        fclose($file_handle);

        unlink($tempzip);
    }

    /**
     * Close the archive.
     * A closed archive can no longer have new files added to it.
     *
     * @return bool $success
     */
    public function finalize() {
        if (!$this->isFinalized) {
            if (strlen($this->streamFilePath) > 0) {
                $this->closeStream();
            }
            $cd = implode("", $this->cdRec);

            $cdRecSize = pack("v", sizeof($this->cdRec));
            $cdRec = $cd . self::ZIP_END_OF_CENTRAL_DIRECTORY
            . $cdRecSize . $cdRecSize
            . pack("VV", strlen($cd), $this->offset);
            if (!empty($this->zipComment)) {
                $cdRec .= pack("v", strlen($this->zipComment)) . $this->zipComment;
            } else {
                $cdRec .= "\x00\x00";
            }

            $this->zipwrite($cdRec);

            $this->isFinalized = TRUE;
            $cd = NULL;
            $this->cdRec = NULL;

            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get the handle ressource for the archive zip file.
     * If the zip haven't been finalized yet, this will cause it to become finalized
     *
     * @return zip file handle
     */
    public function getZipFile() {
        if (!$this->isFinalized) {
            $this->finalize();
        }

        $this->zipflush();

        rewind($this->zipFile);

        return $this->zipFile;
    }

    /**
     * Get the zip file contents
     * If the zip haven't been finalized yet, this will cause it to become finalized
     *
     * @return zip data
     */
    public function getZipData() {
        if (!$this->isFinalized) {
            $this->finalize();
        }
        if (!is_resource($this->zipFile)) {
            return $this->zipData;
        } else {
            rewind($this->zipFile);
            $filestat = fstat($this->zipFile);
            return fread($this->zipFile, $filestat['size']);
        }
    }

    /**
     * Send the archive as a zip download
     *
     * @param String $fileName The name of the Zip archive, ie. "archive.zip".
     * @param String $contentType Content mime type. Optional, defaults to "application/zip".
     * @return bool $success
     */
    function sendZip($fileName, $contentType = "application/zip") {
        if (!$this->isFinalized) {
            $this->finalize();
        }

        $headerFile = null;
        $headerLine = null;
        if (!headers_sent($headerFile, $headerLine) or die("<p><strong>Error:</strong> Unable to send file $fileName. HTML Headers have already been sent from <strong>$headerFile</strong> in line <strong>$headerLine</strong></p>")) {
            if ((ob_get_contents() === FALSE || ob_get_contents() == '') or die("\n<p><strong>Error:</strong> Unable to send file <strong>$fileName.epub</strong>. Output buffer contains the following text (typically warnings or errors):<br>" . ob_get_contents() . "</p>")) {
                if (ini_get('zlib.output_compression')) {
                    ini_set('zlib.output_compression', 'Off');
                }

                header("Pragma: public");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s T"));
                header("Expires: 0");
                header("Accept-Ranges: bytes");
                header("Connection: close");
                header("Content-Type: " . $contentType);
                header('Content-Disposition: attachment; filename="' . $fileName . '";');
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ". $this->getArchiveSize());

                if (!is_resource($this->zipFile)) {
                    echo $this->zipData;
                } else {
                    rewind($this->zipFile);

                    while (!feof($this->zipFile)) {
                        echo fread($this->zipFile, $this->streamChunkSize);
                    }
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Return the current size of the archive
     *
     * @return $size Size of the archive
     */
    public function getArchiveSize() {
        if (!is_resource($this->zipFile)) {
            return strlen($this->zipData);
        }
        $filestat = fstat($this->zipFile);

        return $filestat['size'];
    }

    /**
     * Calculate the 2 byte dostime used in the zip entries.
     *
     * @param int $timestamp
     * @return 2-byte encoded DOS Date
     */
    private function getDosTime($timestamp = 0) {
        $timestamp = (int)$timestamp;
        $oldTZ = @date_default_timezone_get();
        date_default_timezone_set('UTC');
        $date = ($timestamp == 0 ? getdate() : getdate($timestamp));
        date_default_timezone_set($oldTZ);
        if ($date["year"] >= 1980) {
            return pack("V", (($date["mday"] + ($date["mon"] << 5) + (($date["year"]-1980) << 9)) << 16) |
                    (($date["seconds"] >> 1) + ($date["minutes"] << 5) + ($date["hours"] << 11)));
        }
        return "\x00\x00\x00\x00";
    }

    /**
     * Build the Zip file structures
     *
     * @param String $filePath
     * @param String $fileComment
     * @param String $gpFlags
     * @param String $gzType
     * @param int $timestamp
     * @param string $fileCRC32
     * @param int $gzLength
     * @param int $dataLength
     * @param integer $extFileAttr Use self::EXT_FILE_ATTR_FILE for files, self::EXT_FILE_ATTR_DIR for Directories.
     */
    private function buildZipEntry($filePath, $fileComment, $gpFlags, $gzType, $timestamp, $fileCRC32, $gzLength, $dataLength, $extFileAttr) {
        $filePath = str_replace("\\", "/", $filePath);
        $fileCommentLength = (empty($fileComment) ? 0 : strlen($fileComment));
        $timestamp = (int)$timestamp;
        $timestamp = ($timestamp == 0 ? time() : $timestamp);

        $dosTime = $this->getDosTime($timestamp);
        $tsPack = pack("V", $timestamp);

        $ux = "\x75\x78\x0B\x00\x01\x04\xE8\x03\x00\x00\x04\x00\x00\x00\x00";

        if (!isset($gpFlags) || strlen($gpFlags) != 2) {
            $gpFlags = "\x00\x00";
        }

        $isFileUTF8 = mb_check_encoding($filePath, "UTF-8") && !mb_check_encoding($filePath, "ASCII");
        $isCommentUTF8 = !empty($fileComment) && mb_check_encoding($fileComment, "UTF-8") && !mb_check_encoding($fileComment, "ASCII");
        if ($isFileUTF8 || $isCommentUTF8) {
            $flag = 0;
            $gpFlagsV = unpack("vflags", $gpFlags);
            if (isset($gpFlagsV['flags'])) {
                $flag = $gpFlagsV['flags'];
            }
            $gpFlags = pack("v", $flag | (1 << 11));
        }
        
        $header = $gpFlags . $gzType . $dosTime. $fileCRC32
        . pack("VVv", $gzLength, $dataLength, strlen($filePath)); // File name length

        $zipEntry  = self::ZIP_LOCAL_FILE_HEADER;
        $zipEntry .= self::ATTR_VERSION_TO_EXTRACT;
        $zipEntry .= $header;
        $zipEntry .= pack("v", ($this->addExtraField ? 28 : 0)); // Extra field length
        $zipEntry .= $filePath; // FileName
        // Extra fields
        if ($this->addExtraField) {
            $zipEntry .= "\x55\x54\x09\x00\x03" . $tsPack . $tsPack . $ux;
        }
        $this->zipwrite($zipEntry);

        $cdEntry  = self::ZIP_CENTRAL_FILE_HEADER;
        $cdEntry .= self::ATTR_MADE_BY_VERSION;
        $cdEntry .= ($dataLength === 0 ? "\x0A\x00" : self::ATTR_VERSION_TO_EXTRACT);
        $cdEntry .= $header;
        $cdEntry .= pack("v", ($this->addExtraField ? 24 : 0)); // Extra field length
        $cdEntry .= pack("v", $fileCommentLength); // File comment length
        $cdEntry .= "\x00\x00"; // Disk number start
        $cdEntry .= "\x00\x00"; // internal file attributes
        $cdEntry .= $extFileAttr; // External file attributes
        $cdEntry .= pack("V", $this->offset); // Relative offset of local header
        $cdEntry .= $filePath; // FileName
        // Extra fields
        if ($this->addExtraField) {
            $cdEntry .= "\x55\x54\x05\x00\x03" . $tsPack . $ux;
        }
        if (!empty($fileComment)) {
            $cdEntry .= $fileComment; // Comment
        }

        $this->cdRec[] = $cdEntry;
        $this->offset += strlen($zipEntry) + $gzLength;
    }

    private function zipwrite($data) {
        if (!is_resource($this->zipFile)) {
            $this->zipData .= $data;
        } else {
            fwrite($this->zipFile, $data);
            fflush($this->zipFile);
        }
    }

    private function zipflush() {
        if (!is_resource($this->zipFile)) {
            $this->zipFile = tmpfile();
            fwrite($this->zipFile, $this->zipData);
            $this->zipData = NULL;
        }
    }

    /**
     * Join $file to $dir path, and clean up any excess slashes.
     *
     * @param String $dir
     * @param String $file
     */
    public static function pathJoin($dir, $file) {
        if (empty($dir) || empty($file)) {
            return self::getRelativePath($dir . $file);
        }
        return self::getRelativePath($dir . '/' . $file);
    }

    /**
     * Clean up a path, removing any unnecessary elements such as /./, // or redundant ../ segments.
     * If the path starts with a "/", it is deemed an absolute path and any /../ in the beginning is stripped off.
     * The returned path will not end in a "/".
     *
     * @param String $path The path to clean up
     * @return String the clean path
     */
    public static function getRelativePath($path) {
        $path = preg_replace("#/+\.?/+#", "/", str_replace("\\", "/", $path));
        $dirs = explode("/", rtrim(preg_replace('#^(?:\./)+#', '', $path), '/'));

        $offset = 0;
        $sub = 0;
        $subOffset = 0;
        $root = "";

        if (empty($dirs[0])) {
            $root = "/";
            $dirs = array_splice($dirs, 1);
        } else if (preg_match("#[A-Za-z]:#", $dirs[0])) {
            $root = strtoupper($dirs[0]) . "/";
            $dirs = array_splice($dirs, 1);
        }

        $newDirs = array();
        foreach ($dirs as $dir) {
            if ($dir !== "..") {
                $subOffset--;
                $newDirs[++$offset] = $dir;
            } else {
                $subOffset++;
                if (--$offset < 0) {
                    $offset = 0;
                    if ($subOffset > $sub) {
                        $sub++;
                    }
                }
            }
        }

        if (empty($root)) {
            $root = str_repeat("../", $sub);
        }
        return $root . implode("/", array_slice($newDirs, 0, $offset));
    }
}
?>