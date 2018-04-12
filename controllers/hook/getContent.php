    <?php

    class WeekSpecialsGetContentController
    {
        public function __construct($module, $file, $path)
        {
            $this->file=$file;
            $this->module=$module;
            $this->context=Context::getContext();
            $this->path=$path;
        }
        // Page de config
            // Enregistrement des paramètres de configuration
        public function processConfiguration()
        {
            if(Tools::isSubmit('submit_weekspecials_config'))
            {
                $allergens=Configuration::get('WEEKS_ALLERG');
                var_dump($allergens);
                $allergens=unserialize($allergens);
                var_dump($allergens);
                // die;
                $enable_front=Tools::getValue('enable_front');
                $enable_template=Tools::getValue('template');
                $nb_dishes=Tools::getValue('dishes');
                $allergens=array(Tools::getValue('allergens')); //TODO:
                $allergens=serialize($allergens);
                Configuration::updateValue('WEEKS_ENABLE', $enable_front);
                Configuration::updateValue('WEEKS_TEMPLATE', $enable_template);
                Configuration::updateValue('WEEKS_DISHES', $nb_dishes);
                Configuration::updateValue('WEEKS_ALLERG', $allergens);
                $this->context->smarty->assign('confirmation','true');
            }
        }
        
        // Saves the submitted content to db
        public function processForms()
        {
            if(Tools::isSubmit('submit_weekspecials_content'))
            {
                $input=Tools::getAllValues();
                $menu=$input['menu'];
                $menu=serialize($input['menu']);
                if(!Validate::isString($menu)){
                    $this->context->smarty->assign('error','error');
                }
                $WeekSpecial=new WeekSpecial();
                $WeekSpecial->array_weekspecials_menu=$menu;
                $WeekSpecial->add();
            }
            
        }    
            // Submit template modifications && display .tpl content in textarea
        public function assignTemplate()
        {
            $path=_PS_MODULE_DIR_.'weekspecials/views/templates/hook/displayHomeTab.tpl';
            $tpl=file_get_contents($path);
            if(Tools::isSubmit('submit_weekspecials_template'))
            {
                $tpl=Tools::getValue('template');
                $file=fopen($path, 'w+');
                fwrite($file,$tpl);
                fclose($file);
            } 
            elseif(Tools::isSubmit('submit_weekspecials_reset'))
            {
                $tpl=file_get_contents(_PS_MODULE_DIR_.'weekspecials/displayHomeTab.bak.tpl');
                $file=fopen($path,'w+');
                fwrite($file, $tpl);
                fclose($file);            
            }
            $this->context->smarty->assign('tpl',$tpl);
        }

            // Affichage back
        public function run()
        {
            $days=array('monday','tuesday','wednesday','thursday','friday');
            $this->context->smarty->assign('days', $days);
            $courses=array('first_course','salad','dish');
            $this->context->smarty->assign('courses',$courses);
            $allergies=array('Gluten', 'Crustacés', 'Oeufs','Poisson','Arachides','Soja','Lait','Fruits à coque','Céleri','Moutarde','Sésame','Sulfites','Lupin','Mollusques');
            sort($allergies);
            $this->context->smarty->assign('allergies', $allergies);
            $this->processConfiguration();
            $this->processForms();
            // $this->assignTemplate();
            // $this->hookDisplayHomeTab();
            $path=_PS_MODULE_DIR_.'weekspecials/views/templates/hook/displayHomeTab.tpl';
            $this->context->smarty->assign('path',$path);
            $html_form=$this->renderForm();
            $this->context->smarty->assign('config',$html_form);
            $enable_template=Configuration::get('WEEKS_TEMPLATE');
            $this->context->smarty->assign('enable_template', $enable_template);

            return $this->module->display($this->file, 'getContent.tpl');
        }

            // handles back office configuration Form
        public function renderForm()
        {
            // $allergens=array('coucou','tu','veux','voir','mes','allergenes');
            // $allergens=implode(', ',$allergens);
            $fields_form=array(
                'form'=>array(
                    'legend'=>array(
                        'title'=>$this->module->l('Weekly specials configuration'),
                        'icon' =>'icon-wrench'
                    ),
                    'input'=>array(
                        array(
                            'type'=>'switch',
                            'label'=>$this->module->l('Enable Module'),
                            'name'=>'enable_front',
                            'desc'=>$this->module->l('Displays module in the front-office'),
                            'values'=>array(
                                array(
                                    'id'=>'enable_1',
                                    'value'=> 1,
                                    'label'=>$this->module->l('Enabled'),
                                ), array(
                                    'id'=> 'enable_0',
                                    'value'=>0,
                                    'label'=>$this->module->l('Disabled'),
                                )
                            ),
                        ), array(
                            'type'=>'switch',
                            'label'=>$this->module->l('Enable Template Editor'),
                            'name'=>'template',
                            'desc'=>$this->module->l('Enables edition of the template'),
                            'values'=>array(
                                array(
                                    'id'=>'template_1',
                                    'value'=>1,
                                    'label'=>$this->module->l('Enabled'),
                                ), array(
                                    'id'=>'template_0',
                                    'value'=>0,
                                    'label'=>$this->module->l('Disabled'),
                                )
                            ),
                        ), array(
                            'type'=>'select',
                            'label'=>$this->module->l('Select the number of courses'),
                            'name'=>'dishes',
                            'desc'=>$this->module->l('Allows you to select the number of dishes per day (their name are set in the admin page)'),
                            'options'=>array(
                                'query'=>array('1','2','3','4'),
                                'id'=>'nb_dishes',
                            ),
                        ), array(
                            'type'=>'text',
                            'label'=>$this->module->l('Set allergens'),
                            'name'=>'allergens',
                            'desc'=>$this->module->l('Sets allergens, default allergens are '),//TODO:
                        )
                    ),
                    'submit'=>array(
                        'title'=>$this->module->l('Save'),
                    )
                ),
            );

            $helper=new HelperForm();
            $helper->table='weekspecials_config';
            $helper->default_form_language=(int)Configuration::get('PS_LANG_DEFAULT');
            $helper->allow_employee_form_lang=(int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
            $helper->submit_action='submit_weekspecials_config';
            $helper->currentIndex=$this->context->link->getAdminLink('AdminModules',false).'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;
            $helper->token=Tools::getAdminTokenLite('AdminModules');
            $helper->tpl_vars=array(
                'fields_value'=>array(
                    'enable_front'=>Tools::getValue('enable_front', Configuration::get('WEEKS_ENABLE')),
                    'template'=>Tools::getValue('template', Configuration::get('WEEKS_TEMPLATE')),
                    'dishes'=> Tools::getValue('dishes', Configuration::get('WEEKS_DISHES')),
                ),
                'languages'=>$this->context->controller->getLanguages()
            );
            return $helper->generateForm(array($fields_form));
        }

    }
