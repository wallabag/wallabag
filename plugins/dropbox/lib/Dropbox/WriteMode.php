<?php
namespace Dropbox;

/**
 * Describes how a file should be saved when it is written to Dropbox.
 */
final class WriteMode
{
    /**
     * The URL parameters to pass to the file uploading endpoint to achieve the
     * desired write mode.
     *
     * @var array
     */
    private $extraParams;

    /**
     * @internal
     */
    private function __construct($extraParams)
    {
        $this->extraParams = $extraParams;
    }

    /**
     * @internal
     */
    function getExtraParams()
    {
        return $this->extraParams;
    }

    /**
     * Returns a {@link WriteMode} for adding a new file.  If a file at the specified path already
     * exists, the new file will be renamed automatically.
     *
     * For example, if you're trying to upload a file to "/Notes/Groceries.txt", but there's
     * already a file there, your file will be written to "/Notes/Groceries (1).txt".
     *
     * You can determine whether your file was renamed by checking the "path" field of the
     * metadata object returned by the API call.
     *
     * @return WriteMode
     */
    static function add()
    {
        if (self::$addInstance === null) {
            self::$addInstance = new WriteMode(array("overwrite" => "false"));
        }
        return self::$addInstance;
    }
    private static $addInstance = null;

    /**
     * Returns a {@link WriteMode} for forcing a file to be at a certain path.  If there's already
     * a file at that path, the existing file will be overwritten.  If there's a folder at that
     * path, however, it will not be overwritten and the API call will fail.
     *
     * @return WriteMode
     */
    static function force()
    {
        if (self::$forceInstance === null) {
            self::$forceInstance = new WriteMode(array("overwrite" => "true"));
        }
        return self::$forceInstance;
    }
    private static $forceInstance = null;

    /**
     * Returns a {@link WriteMode} for updating an existing file.  This is useful for when you
     * have downloaded a file, made modifications, and want to save your modifications back to
     * Dropbox.  You need to specify the revision of the copy of the file you downloaded (it's
     * the "rev" parameter of the file's metadata object).
     *
     * If, when you attempt to save, the revision of the file currently on Dropbox matches
     * $revToReplace, the file on Dropbox will be overwritten with the new contents you provide.
     *
     * If the revision of the file currently on Dropbox doesn't match $revToReplace, Dropbox will
     * create a new file and save your contents to that file.  For example, if the original file
     * path is "/Notes/Groceries.txt", the new file's path might be
     * "/Notes/Groceries (conflicted copy).txt".
     *
     * You can determine whether your file was renamed by checking the "path" field of the
     * metadata object returned by the API call.
     *
     * @param string $revToReplace
     * @return WriteMode
     */
    static function update($revToReplace)
    {
        return new WriteMode(array("parent_rev" => $revToReplace));
    }

    /**
     * Check that a function argument is of type <code>WriteMode</code>.
     *
     * @internal
     */
    static function checkArg($argName, $argValue)
    {
        if (!($argValue instanceof self)) Checker::throwError($argName, $argValue, __CLASS__);
    }

    /**
     * Check that a function argument is either <code>null</code> or of type
     * <code>WriteMode</code>.
     *
     * @internal
     */
    static function checkArgOrNull($argName, $argValue)
    {
        if ($argValue === null) return;
        if (!($argValue instanceof self)) Checker::throwError($argName, $argValue, __CLASS__);
    }
}
