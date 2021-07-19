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
        $this->MODULE_VERSION = "1.0.1";
        $this->MODULE_VERSION_DATE = "2021-02-19 06:35:34";
        $this->MODULE_NAME = "Генератор bx.model";
        $this->MODULE_DESCRIPTION = "";
    }

    /**
     * @param string $message
     */
    public function setError(string $message)
    {
        $GLOBALS["APPLICATION"]->ThrowException($message);
    }

    /**
     * @return bool
     */
    public function DoInstall(): bool
    {
        $result = $this->installRequiredModules();
        if (!$result) {
            return false;
        }

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * @return bool
     */
    public function DoUninstall(): bool
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * @return bool
     */
    public function installRequiredModules(): bool
    {
        $isInstalled = ModuleManager::isModuleInstalled('bx.model');
        if ($isInstalled) {
            return true;
        }

        $modulePath = getLocalPath("modules/bx.model/install/index.php");
        if (!$modulePath) {
            $this->setError('Отсутствует модуль bx.model - https://github.com/beta-eto-code/bx.model');
            return false;
        }

        require_once $modulePath;
        $moduleInstaller = new bx_model();
        $resultInstall = (bool)$moduleInstaller->DoInstall();
        if (!$resultInstall) {
            $this->setError('Ошибка установки модуля bx.model');
        }

        return $resultInstall;
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
