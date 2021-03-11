<?

IncludeModuleLangFile(__FILE__);
use \Bitrix\Main\ModuleManager;

class bx_model_gen extends CModule
{
    public $MODULE_ID = "bx.model.gen";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $errors;

    public function __construct()
    {
        $this->MODULE_VERSION = "0.0.1";
        $this->MODULE_VERSION_DATE = "2021-02-19 06:35:34";
        $this->MODULE_NAME = "Генератор bx.model";
        $this->MODULE_DESCRIPTION = "";
    }

    public function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    public function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallDB()
    {
    }

    public function UnInstallDB()
    {
    }

    public function InstallEvents()
    {
        return true;
    }

    public function UnInstallEvents()
    {
        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . "/files", $_SERVER["DOCUMENT_ROOT"]);
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . "/files", $_SERVER["DOCUMENT_ROOT"]);
        return true;
    }
}
