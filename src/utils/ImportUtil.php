<?php declare(strict_types=1);

namespace VitesseCms\Import\Utils;

use http\Client\Request;
use VitesseCms\Content\Forms\ItemForm;
use VitesseCms\Content\Models\Item;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Form\AbstractForm;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;

class ImportUtil
{
    public static function getFieldsFromForm(AbstractForm $form, string $className): array
    {
        $fields = [
            [
                'name' => 'published',
                'multilang' => false,
                'element' => Check::class,
            ],
            [
                'name' => 'parentId',
                'multilang' => false,
                'element' => Text::class,
            ],
        ];
        foreach ($form->getElements() as $element) :
            if (
                !empty($element->getName())
                && get_class($element) != 'Phalcon\Forms\Element\Submit'
            ) :
                $field = [
                    'name' => $element->getName(),
                    'multilang' => $element->getAttribute('multilang'),
                    'element' => get_class($element),
                ];
                if ($className === Item::class) :
                    Datafield::setFindPublished(false);
                    Datafield::setFindValue('calling_name', $element->getName());
                    $field['datafield'] = Datafield::findFirst();
                endif;
                $fields[] = $field;
            endif;
        endforeach;

        return $fields;
    }

    public static function getFormFromClass(string $className, Request $request): AbstractForm
    {
        if ($className === Item::class) :
            $file = $request->getUploadedFiles()[0]->getTempName();
            $item = null;
            if (($handle = fopen($file, "r")) !== false) :
                $row = 1;
                $mappedFields = [];
                while (
                    (($data = fgetcsv($handle, 1000, ",")) !== false)
                    && $item == null
                ) :
                    if ($row === 1) :
                        $mappedFields = array_flip($data);
                    else :
                        if (isset($data[$mappedFields['id']]) && !empty($data[$mappedFields['id']])) :
                            Item::setFindPublished(false);
                            $item = Item::findById($data[$mappedFields['id']]);
                        endif;
                    endif;
                    $row++;
                endwhile;
            endif;

            return new ItemForm($item);
        endif;

        $formName = SystemUtil::getFormclassFromClass($className);

        return new $formName();
    }

    public static function getImporters(array $directories): array
    {
        $files = [];
        foreach ($directories as $directory) :
            $files = array_merge($files, DirectoryUtil::getFilelist($directory));
        endforeach;
        ksort($files);

        $newReturn = [];
        foreach ($files as $filePath => $fileName) :
            if (
                substr_count($fileName, 'Abstract') === 0
                && substr_count($fileName, 'Interface') === 0
            ) :
                $newReturn[SystemUtil::createNamespaceFromPath($filePath)] = str_replace('ImportHelper.php', '', $fileName);
            endif;
        endforeach;

        return $newReturn;
    }
}
