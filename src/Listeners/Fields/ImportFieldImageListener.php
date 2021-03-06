<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners\Fields;

use VitesseCms\Configuration\Services\ConfigServiceInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Import\Models\ImportDatafield;
use Phalcon\Events\Event;

class ImportFieldImageListener
{
    /**
     * @var ConfigServiceInterface
     */
    private $configurationService;

    /**
     * @var UrlService
     */
    private $urlService;

    public function __construct(
        ConfigServiceInterface $configService,
        UrlService $urlService
    ){
        $this->configurationService = $configService;
        $this->urlService = $urlService;
    }

    public function parse(Event $event, Item $item, ImportDatafield $importDatafield): void
    {
        $configuration = $this->configurationService;
        $url = $this->urlService;

        $itemValue = $item->_($importDatafield->getCallingName());
        if (
            !empty($itemValue)
            && (
                strpos($itemValue, 'http') === 0
                || strpos($itemValue, 'www.') === 0
            )
        ) :
            $ext = FileUtil::getExtension($itemValue);
            $filename = FileUtil::sanatize($item->getNameField() . '.' . $ext);
            $target = $configuration->getUploadDir() . $importDatafield->getImageFolder() . '/' . $filename;
            if (!is_file($target)) :
                if (
                    strpos($itemValue, 'www.') === 0
                    && strpos($itemValue, 'https://') === false
                ) :
                    $itemValue = 'https://' . $itemValue;
                endif;

                if ($url->exists($itemValue)):
                    FileUtil::copy($itemValue, $target);
                    $item->set($importDatafield->getCallingName(), $importDatafield->getImageFolder() . '/' . $filename);
                else :
                    $item->set($importDatafield->getCallingName(), '');
                endif;
            else :
                $item->set($importDatafield->getCallingName(), $importDatafield->getImageFolder() . '/' . $filename);
            endif;
        endif;
    }
}
