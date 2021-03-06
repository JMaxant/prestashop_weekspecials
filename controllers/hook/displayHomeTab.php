<?php

class WeekSpecialsDisplayHomeTabController
{
    public function __construct($module, $file, $path)
    {
        $this->file=$file;
        $this->module=$module;
        $this->context=Context::getContext();
        $this->path=$path;
    }

    public function setMedia()
    {
        $dirPath=__PS_BASE_URI__.'modules/weekspecials/';
        $this->context->controller->addCSS($dirPath.'views/css/styles.css','all');
        $this->context->controller->addJS($dirPath.'views/js/app.js');
    }    

    // affichage hook displayHomeTab
    public function assignTemplate() //FIXME:
    {
        $req=WeekSpecial::getAll();
        $courses=unserialize($req['courses_weekspecials_menu']);
        $output=unserialize($req['array_weekspecials_menu']);
        $args=array_keys($output);;
        foreach($args as $arg){
            if($arg=='date'){
                $dates=$output[$arg];
                if(!empty($dates[0])&&!empty($dates[1])){
                    foreach($dates as $date){
                        $date=explode('-',$date);
                        $formatDates[]=$date[2].'/'.$date[1].'/'.$date[0];
                    }
                }
            }else{
                $menu[]=$output[$arg];
            }
        }
        
        $ws_days=$this->module->days;

        $this->context->smarty->assign('dates',$formatDates);
        $this->context->smarty->assign('menu', $menu);
        $this->context->smarty->assign('courses',$courses);
        $this->context->smarty->assign('ws_days',$ws_days);
    }

    public function run()
    {   
       
        $this->setMedia();
        $this->assignTemplate();

        return $this->module->display($this->file, 'displayHomeTab.tpl');
    }
    
}