<?php

    class home extends Controller
    {
        public $layout = 'default';
        
        
        public function main($a=1,$b=2, $c=3)
         {
			 
			 
			 //(new User($this->post['user_id']))->delete();
				
			 
            print "$a,$b,$c";
           /*
            
            (new Organization)
              ->set_title('Test Org')
              ->set_description('RADSYS 4 test')
              ->set_created_at('2021-10-13 06:35:38')
              ->set_updated_at('2021-10-13 06:35:38')              
              ->save();
              
            (new User)
              ->set_name('Khan')
              ->set_organization_id(1)
              ->set_role('admin')
              ->set_fake(1)
              ->set_email_verified_at('2021-10-13 06:35:38')
              ->set_created_at('2021-10-13 06:35:38')
              ->set_updated_at('2021-10-13 06:35:38')                
              ->save();
              
              
            /*(new Setting)
              ->set_option_key('key'.time())
              ->set_option_values('key'.time())
              ->set_created_at('2021-10-13 06:35:38')
              ->set_updated_at('2021-10-13 06:35:38')
              ->save();
              
              (new Setting(1))->delete();
              (new Setting(2))
                ->set_option_key('NEW KEY')
                ->save();*/
            

                
            //print $this->get_org_pictures_folder(1) ? 'yes' : 'no';
            
            $this->render('main', 'CONTENT');
        }
    }


