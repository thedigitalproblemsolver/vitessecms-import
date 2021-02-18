<?php declare(strict_types=1);

namespace VitesseCms\Import\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Import\Forms\UploadFileForm;
use VitesseCms\Import\Helpers\AbstractImportHelperInterface;
use VitesseCms\Import\Utils\ImportUtil;

class AdminindexController extends AbstractAdminController
{
    public function indexAction(): void
    {
        $this->view->setVar('content', $this->view->renderTemplate(
            'import_menu',
            $this->configuration->getVendorNameDir() . 'import/src/Resources/views/admin/'
        ));
        $this->prepareView();
    }

    public function fileformAction(): void
    {
        $this->view->setVar('content', (new UploadFileForm())->renderForm(
            'admin/import/adminindex/parseImport',
            'importForm',
            true
        ));
        $this->prepareView();
    }

    public function parseImportAction(): void
    {
        if ($this->request->isPost()) :
            if (count($this->request->getUploadedFiles()) === 1) :
                $file = $this->request->getUploadedFiles()[0];
                $filenameParts = explode('_', $file->getName());
                if (count($filenameParts) === 6) :
                    unset($filenameParts[0]);
                    $filenameParts = array_reverse($filenameParts);
                    [$languageLocale, $importType] = explode('.', $filenameParts[0]);
                    unset($filenameParts[0]);
                    $filenameParts = array_reverse($filenameParts);
                    $className = implode('\\', $filenameParts);

                    $helperClass = 'VitesseCms\\Import\\Helpers\\' . ucfirst($importType) . 'ImportHelper';
                    /** @var AbstractImportHelperInterface $importHelper */
                    $importHelper = new $helperClass();
                    $importHelper->setLanguage($languageLocale);
                    $importHelper->setFile($file);
                    $importHelper->setClass($className);
                    $importHelper->setFields(
                        ImportUtil::getFieldsFromForm(
                            ImportUtil::getFormFromClass($className, $this->request),
                            $className
                        )
                    );
                    $importHelper->processImport();
                    $this->flash->setSucces('ADMIN_FILE_IMPORT_SUCCESS');
                else :
                    $this->flash->setError('ADMIN_FILE_IMPORT_FAILED');
                endif;
            else :
                $this->flash->setError('ADMIN_FILE_IMPORT_FAILED');
            endif;
        endif;

        $this->redirect();
    }
}
