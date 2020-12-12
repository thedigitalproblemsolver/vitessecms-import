<?php declare(strict_types=1);

namespace VitesseCms\Import\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class UploadFileForm extends AbstractForm
{

    public function initialize()
    {
        $this->addUpload(
            '%ADMIN_SELECT_A_FILE%',
            'file',
            (new Attributes())->setRequired(true)
        )->addSubmitButton('%ADMIN_UPLOAD_FILE%');
    }
}
