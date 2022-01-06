<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once PS_ADMIN_DIR.'/../classes/AdminTab.php';
require_once _PS_MODULE_DIR_.'/jbx_superuser/jbx_superuser.php';

class AdminSuperUser extends AdminTab
{
    private $module = 'jbx_superuser';

    public function __construct()
    {
        global $cookie, $_LANGADM;
        $langFile = _PS_MODULE_DIR_.$this->module.'/'.Language::getIsoById(intval($cookie->id_lang)).'.php';

        if (file_exists($langFile)) {
            require_once $langFile;

            foreach ($_MODULE as $key => $value) {
                if (Tools::substr(strip_tags($key), 0, 5) == 'Admin') {
                    $_LANGADM[str_replace('_', '', strip_tags($key))] = $value;
                }
            }
        }

        parent::__construct();
    }

    public function display()
    {
        $module = new jbx_superuser;
        echo $module->getContent();
    }
}
