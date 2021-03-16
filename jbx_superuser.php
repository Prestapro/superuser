<?php
class jbx_superuser extends Module
{
	public function __construct()
	{
		$this->name = 'jbx_superuser';
		$this->tab = 'Julien Breux Developpement';
		$this->version = 1.6;
		$this->page = basename(__FILE__, '.php');

		parent::__construct();

		$this->displayName = $this->l('Super User');
		$this->description = $this->l('You are THE super user !');
	}

	public function install()
	{
    if(version_compare(_PS_VERSION_, '1.2.0.0', '>=')) {
      $this->installModuleTab('AdminSuperUser', 2);
    }
		if(!parent::install() || 
       !$this->registerHook('adminCustomers') || 
       !$this->registerHook('adminOrder'))
			return false;
		return true;
	}

	public function uninstall()
	{
    if(version_compare(_PS_VERSION_, '1.2.0.0', '>=')) {
      !$this->uninstallModuleTab('AdminSuperUser');
    }
		return parent::uninstall();
	}

	private function installModuleTab($tabClass, $idTabParent)
	{
    $names = 
		@copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$tabClass.'.gif');
		$tab = new Tab();
    $names = array(1=>'SuperUser', 'SuperUtilisateur');
    foreach (Language::getLanguages() as $language) {
      $tab->name[$language['id_lang']] = isset($names[$language['id_lang']]) ? $names[$language['id_lang']] : $names[1];
    }
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $idTabParent;
		if(!$tab->save())
			return false;
		return true;
	}

	private function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);
		if($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();
			@unlink(_PS_IMG_DIR_.'t/'.$tabClass.'.gif');
			return true;
		}
		return false;
	}

private function _postProceed()
{
$cookie_lifetime = (int)(defined('_PS_ADMIN_DIR_') ? Configuration::get('PS_COOKIE_LIFETIME_BO') : Configuration::get('PS_COOKIE_LIFETIME_FO'));
$cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
if (Context::getContext()->shop->getGroup()->share_order)
  $cookie = new Cookie('ps-sg'.Context::getContext()->shop->getGroup()->id, '', $cookie_lifetime, Context::getContext()->shop->getUrlsSharedCart());
else
{
  $domains = null;
  if (Context::getContext()->shop->domain != Context::getContext()->shop->domain_ssl)
        $domains = array(Context::getContext()->shop->domain_ssl, Context::getContext()->shop->domain);

  $cookie = new Cookie('ps-s'.Context::getContext()->shop->id, '', $cookie_lifetime, $domains);
}

  if(Tools::isSubmit('submitSuperUser'))
  {
   if($cookie->logged)
   $cookie->logout();
   Tools::setCookieLanguage();
   Tools::switchLanguage();
   $customer = new Customer(intval(Tools::getValue('id_customer')));
   $cookie->id_customer = intval($customer->id);
   $cookie->customer_lastname = $customer->lastname;
   $cookie->customer_firstname = $customer->firstname;
   $cookie->logged = 1;
   $cookie->passwd = $customer->passwd;
   $cookie->email = $customer->email;
   if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0))
        $cookie->id_cart = Cart::lastNoneOrderedCart($customer->id);
          if (Tools::getIsset('used_last_cart')) {
        $cookie->id_cart = $customer->getLastCart();
   }
  }
  return $cookie;
}

	public function getContent()
	{
		$html = '';
		$cookie = $this->_postProceed();
		$html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="../img/admin/profiles.png" alt="" title="" />'.$this->l('Customers').'</legend>';
		if($cookie->logged)
			$html .= '<b>'.$this->l('You are').' '.$cookie->customer_lastname.' '.$cookie->customer_firstname.'.</b> <a href="'.__PS_BASE_URI__.'" target="_blank">'.$this->l('Go to front office !').'</a><br /><br />';
		$html .= '<label>'.$this->l('Customers List').'</label>
			<div class="margin-form">';
		$html .= '<select name="id_customer">';
		foreach(Customer::getCustomers() as $customer)
			$html .= '<option value="'.$customer['id_customer'].'"'.(($cookie->logged AND $cookie->id_customer == $customer['id_customer']) ? ' selected=""' : '').'>'.$customer['id_customer'].' - '.$customer['firstname'].' '.$customer['lastname'].'</option>';
		$html .= '</select>';
		$html .= '</div>
			<label for="used_last_cart">' . $this->l('Used the last cart') . '</label>
			<div class="margin-form">
				<input type="checkbox" name="used_last_cart" id="used_last_cart" value="1" checked="" />
			</div>
			<p class="center">
			<input type="submit" name="submitSuperUser" value="'.$this->l('Login').'" class="button" />
			</p>
			</fieldset>
			</form>';
		$html .= $this->about();
		return $html;
	}

	public function about()
	{
		$html = '<br />
			<fieldset><legend><img src="../img/admin/unknown.gif" alt="" title="" />'.$this->l('About').'</legend>
			Julien BREUX - <a href="http://www.julien-breux.com/2009/05/14/module-prestashop-superuser/?utm_source=Prestashop%2BBackOffice%20Modules&utm_medium=Link&utm_campaign=Module%2BSuperUser">'.$this->l('Website').'</a>
			</fieldset>';

		return $html;
	}

	public function hookadminCustomers($param)
	{
    $cookie = $this->_postProceed();
		if(isset($param['id_customer'])) {
			$id_customer = (int)$param['id_customer'];
			$customer = new Customer($id_customer);
		  echo '<h2>' . $this->l('Connexion') . '</h2>
			<form method="post" action="">
				<p>';
					if($cookie->logged && $cookie->id_customer == $id_customer)
						echo $this->l('You are').' '.$customer->firstname.' '.$customer->lastname.'<br />';
					echo '
					<input type="hidden" name="id_customer" value="'.$id_customer.'" />
          <label for="used_last_cart" class="t">' . $this->l('Used the last cart') . '</label>
          <input type="checkbox" name="used_last_cart" id="used_last_cart" value="1" checked="" /><br />
          <input type="submit" name="submitSuperUser" value="'.$this->l('Connect as ').$customer->firstname.' '.$customer->lastname.'" class="button" />
				</p>
			</form>';
		}
		else {
			$order = new Order($param['id_order']);
			$id_customer = (int)$order->id_customer;
			$customer = new Customer($id_customer);
			echo '<br /><fieldset style="width: 400px;"><legend><img alt="'.$this->l('Connexion').'" src="'.$this->_path.'logo.gif"/> '.$this->l('Connexion').'</legend>
  			<form method="post" action="">';
  					if($cookie->logged && intVal($cookie->id_customer) == $id_customer)
  						echo '<p>'.$this->l('You are').' '.$customer->firstname.' '.$customer->lastname.'</p>';
  					echo '
  				<p class="center">
  				  <input type="hidden" name="id_customer" value="'.$id_customer.'" />
            <label for="used_last_cart" class="t">' . $this->l('Used the last cart') . '</label>
            <input type="checkbox" name="used_last_cart" id="used_last_cart" value="1" checked="" /><br />
            <input type="submit" name="submitSuperUser" value="'.$this->l('Connect as ').$customer->firstname.' '.$customer->lastname.'" class="button" />
  				</p>
  			</form>
			</fieldset>';
		}
	}

	public function hookadminOrder($param)
	{
		$this->hookadminCustomers($param);
	}
}
?>