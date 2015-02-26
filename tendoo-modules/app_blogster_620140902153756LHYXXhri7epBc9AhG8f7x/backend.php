<?php
class blogster_backend extends Libraries
{
	public function __construct($data)
	{
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		parent::__construct();
		__extends($this);
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$this->load->library( 'gui' );
		$this->_config();
		$this->data						=	$data;
		$this->instance					=	get_instance();
		$this->opened_module			=	get_core_vars( 'opened_module' );
		$this->data['module']			=	get_core_vars( 'opened_module' );
		$this->news						=	new blogster_library($this->data);
		set_core_vars( 'news' , $this->news );
		$this->data['news']				=&	$this->news; // Deprecated
		$this->read_slug				=	'read';
		$this->options					=	get_meta( 'blogster_settings' );
		set_core_vars( 'blogster_settings' , $this->options );
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$this->data['inner_head']		=	$this->load->view('admin/inner_head',$this->data,true);
		$this->data['lmenu']			=	$this->load->view(VIEWS_DIR.'/admin/left_menu',$this->data,true,TRUE);
		$this->link						=	MODULES_DIR.$this->opened_module['encrypted_dir'].'/';
		/*
			Intégration de la librarie FILE MANAGER : Gestionnaire des fichiers médias.
		*/
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$fileManager					=	get_modules( 'filter_namespace' , 'tendoo_contents' );
		if($fileManager)
		{
			include_once(MODULES_DIR.$fileManager['encrypted_dir'].'/utilities.php');
			set_core_vars( 'fmlib' , new tendoo_contents_utility() ); // Loading library
		}
		// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	}
	private function _config()
	{

	}
	public function index($page	= 1)
	{
		if($this->input->post('draftSelected'))
		{
			if( current_user()->can( 'blogster@edit_posts' ) )
			{
				foreach($_POST['art_id'] as $id)
				{
					if(!$this->news->moveSpeNewsToDraft($id))
					{
						notice('push',hubby_error('Une erreur s\'est produite durant le d&eacute;placement de certaines articles'));
					}
				}
				notice('push',fetch_notice_output('done'));
			}
			else
			{
				notice('push',fetch_notice_output('notForYourPriv'));
			}
		}
		if($this->input->post('publishSelected'))
		{
			if( current_user()->can( 'blogster@edit_posts' ) )
			{
				foreach($_POST['art_id'] as $id)
				{
					if(!$this->news->publishSpeNews($id))
					{
						notice('push',hubby_error('Une erreur s\'est produite durant la publication de certaines articles'));
					}
				}
				notice('push',fetch_notice_output('done'));
			}
			else
			{
				notice('push',fetch_notice_output('notForYourPriv'));
			}
		}
		if($this->input->post('deleteSelected'))
		{
			if( current_user()->can( 'blogster@delete_posts' ) )
			{
				$status	=	array();
				$status['error']	=	0;
				$status['success']	=	0;
				foreach($_POST['art_id'] as $id)
				{
					if($this->news->deleteSpeNews($id))
					{
						$status['success']++;
					}
					else
					{
						$status['error']++;
					}
				}
				notice('push',tendoo_info( sprintf( __( '% post(s) has been deleted, %s post(s) not deleted' ) , $status['success'] , $status['error'] ) ) );
			}
			else
			{
				notice('push',fetch_notice_output('notForYourPriv'));
			}
		}
		// Filtre
		set_core_vars( 'ttNews' , $this->news->countNews() );
		set_core_vars( 'ttMines'  ,	$this->news->countNews( 'mines' ) );
		set_core_vars( 'ttScheduled' ,	$this->news->countNews( 'scheduled' ) );
		set_core_vars( 'ttDraft' ,	$this->news->countNews( 'draft' ) );
		$count	=	get_core_vars( 'ttNews' );
		$filter	=	'default';
		if(isset($_GET['filter']))
		{
			$filter	=	$_GET['filter'];
			if($filter	==	'mines')
			{
				$count	=	get_core_vars( 'ttMines' );
			}
			else if($filter	==	'scheduled')
			{
				$count	=	get_core_vars( 'ttScheduled' );
			}
			else if($filter	==	'scheduled')
			{
				$count	=	get_core_vars( 'ttDraft' );
			}
		}
		
		set_core_vars( 'latestComments' , $this->news->getComments(0,5) );	

		 $paginate		=	pagination_helper( 
		 	riake( 'post_pp' , $this->options , 10 ) , 
			get_core_vars( 'ttNews' ),
			$page,
			module_url( array( 'index' ) , 'blogster' ),
			$this->url->site_url( array( 'error' , 'code' , 'page-404' ) )
		);
		
		set_page('title', __( 'Blogster - Dashboard' ) );
		set_core_vars( 'getNews' ,	$this->news->getNews( $paginate[ 'start' ] , $paginate[ 'end' ] , FALSE , $filter ) );
		set_core_vars( 'body' ,	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/main',$this->data,false,TRUE) );
	}
	public function publish()
	{
		if( current_user()->can( 'blogster@publish_posts' ) )
		{
			js_push_if_not_exists('jquery-ui-1.10.4.custom.min');
			css_push_if_not_exists('jquery-ui-1.10.4.custom.min');
			
			$categories	=	$this->news->getCat();
			if(count($categories) == 0)
			{
				$this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'category','create?notice=noCategoryCreated'));
			}
			set_core_vars( 'categories' , $categories ); // @since 1.5
			 
			set_page('title', __( 'Blogster - Write a new post' ) );
			
			$this->load->library('form_validation');

			$this->form_validation->set_rules('news_name','Intitulé de l\'article','trim|max_length[200]');
			$this->form_validation->set_rules('news_content','Contenu de l\'article','trim|max_length[20000]');
			$this->form_validation->set_rules('push_directly','Choix de l\'action','trim|max_length[10]');		
			$this->form_validation->set_rules('image_link','Lien de l\'image','trim|max_length[1000]');		
			$this->form_validation->set_rules('thumb_link','Lien de l\'image','trim|max_length[1000]');	
				
			if($this->form_validation->run())
			{
				// var_dump( $this->input->post('scheduledDate') , $this->input->post('scheduledTime') );
				$this->data['result']	=	$this->news->publish_posts(
					$this->input->post('news_name'),
					$this->input->post('news_content'),
					$this->input->post('push_directly'),
					$this->input->post('image_link'),
					$this->input->post('thumb_link'),
					isset($_POST['category']) ? $_POST['category'] : array(1), // expecitng Array
					FALSE,
					isset($_POST['artKeyWord']) ? $_POST['artKeyWord'] : false,
					$this->input->post('scheduledDate'),
					$this->input->post('scheduledTime')
				);
				if($this->data['result'])
				{
					$this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'edit',$this->data['result'][0]['ID'].'?info=Article crée. <a href="'.$this->url->site_url(array('tendoo@news','lecture',$this->data['result'][0]['URL_TITLE'].'?mode=preview'.'" style="text-decoration:underline" target="_blank">cliquez pour voir un aperçu</a>'))));
				}
				else
				{
					notice('push',fetch_notice_output('error'));
				}
				
			}
			$this->instance->visual_editor->loadEditor(1);
			// Ajout des fichier du plugin bootstrap mutiselect
			$this->file->js_url	=	$this->url->main_url();
			js_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/js/bootstrap-multiselect');
			$this->file->css_url=	$this->url->main_url();
			css_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/css/bootstrap-multiselect');
			// Loading Bloster Script
			$this->file->js_url	=	$this->url->main_url();
			js_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/js/blogster.script');
			$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/publish',$this->data,false,TRUE,$this);
		}
		else
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
	}
	public function edit($e)
	{
		js_push_if_not_exists('jquery-ui-1.10.4.custom.min');
		css_push_if_not_exists('jquery-ui-1.10.4.custom.min');
		
		if( !current_user_can( 'blogster@edit_posts' ) )
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
		set_core_vars( 'categories' , $this->news->getCat() );
		if(count(get_core_vars( 'categories' ) ) == 0)
		{
			$this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'category','create?notice=noCategoryCreated'));
		}
		// Control Sended Form Datas
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert"><i class="icon-remove"></i></button><i style="font-size:18px;margin-right:5px;" class="icon-warning-sign"></i>', '</div>');

		$this->form_validation->set_rules('news_name','Intitulé de l\'article','trim|max_length[200]');
		$this->form_validation->set_rules('news_content','Contenu de l\'article','trim|max_length[20000]');
		$this->form_validation->set_rules('push_directly','Choix de l\'action','trim|max_length[10]');		
		$this->form_validation->set_rules('image_link','Lien de l\'image','trim|max_length[1000]');		
		$this->form_validation->set_rules('thumb_link','Lien de l\'image','trim|max_length[1000]');		
		// $this->form_validation->set_rules('category','Cat&eacute;gorie','trim|min_length[1]|max_length[200]');	
		$this->form_validation->set_rules('article_id','Identifiant de l\'article','required|min_length[1]');	
		if($this->form_validation->run())
		{
			$this->data['result']	=	$this->news->edit(
				$this->input->post('article_id'),
				$this->input->post('news_name'),
				$this->input->post('news_content'),
				$this->input->post('push_directly'),
				$this->input->post('image_link'),
				$this->input->post('thumb_link'),
				isset($_POST['category']) ? $_POST['category']	:	array(1), // Setting Default categoryArray if not set
				isset($_POST['artKeyWord']) ? $_POST['artKeyWord'] : false,
				$this->input->post('scheduledDate'),
				$this->input->post('scheduledTime')
			);
			if($this->data['result'])
			{
				notice('push',tendoo_success( __( 'Post updated.' ) ) );
			}
			else
			{
				notice('push',fetch_notice_output('error'));
			}
		}
		// Retreiving News Data
		set_core_vars( 'getSpeNews' , $this->news->getSpeNews($e) );
		if(get_core_vars( 'getSpeNews' ) == false)
		{
			module_location(array('index?notice=unknowArticle'));
		}
		// Récupération des mots clés de l'article.
		set_core_vars( 'getKeyWords' , $this->news->getNewsKeyWords($e) );
//		var_dump($this->data['getKeyWords']);
		set_core_vars( 'getNewsCategories' , $this->news->getArticlesRelatedCategory($e) ); 
		// Définition du titre		
		set_page('title', __( 'Blogster - Edit a post' ) );
		// Chargement de l'éditeur
		$this->instance->visual_editor->loadEditor(1);
		// Ajout des fichier du plugin bootstrap mutiselect
		$this->file->js_url	=	$this->url->main_url();
		js_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/js/bootstrap-multiselect');
		$this->file->css_url=	$this->url->main_url();
		css_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/css/bootstrap-multiselect');
		// Loading Bloster Script
		$this->file->js_url	=	$this->url->main_url();
		js_push_if_not_exists(MODULES_DIR.$this->opened_module['encrypted_dir'].'/js/blogster.script');
		
	
		$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/edit',$this->data,false,TRUE,$this);
	}
	public function category($e = 'index',$i = null)
	{
		if( !current_user_can( 'category_manage@blogster' ) )
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
		if($e == 'index')
		{
			if($this->input->post('action') == 'delete')
			{
				$exec	=	$this->news->deleteBulkCat( riake( 'cat_id' , $_POST ) );
				module_location('category?info='. sprintf( __( '%s category(ies) deleted, %s error(s)' ) , $exec['success'] , $exec['error'] ) );
			}
			if($i	==	null): $i		=	1;endif; // affecte un lorsque la page n\'est pas correctement défini
			$page						=&	$i; // don't waste memory
			set_core_vars( 'ttCat' , $this->news->countCat() );
			set_core_vars( 'paginate' ,	$paginate = $this->tendoo->paginate(10, get_core_vars( 'ttCat' ) ,1,'bg-color-blue fg-color-white','bg-color-white fg-color-blue',$page,$this->url->site_url(array('admin','open','modules',$this->opened_module['namespace'],'category','index')).'/',$ajaxis_link=null) );
			
			if( $paginate[3] == FALSE): $this->url->redirect(array('error','code','page-404'));endif; // redirect if page incorrect
			
			set_core_vars( 'getCat' , $this->news->getCat( $paginate[1], $paginate	[2]) );
			
			set_page('title', __( 'Blogster - Manage categories' ) );
			$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/category',$this->data,false,TRUE);
		}
		else if($e == 'create')
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('cat_name', __( 'Category name' ) ,'required|min_length[3]|max_length[50]');
			$this->form_validation->set_rules('cat_description', __( 'Category Description' ),'required|min_length[3]|max_length[200]');
			if($this->form_validation->run())
			{
				$result	=	$this->news->createCat(
					$this->input->post('cat_name'),
					$this->input->post('cat_description')
				);
				if( $result ){
					notice( 'push' , fetch_notice_output( 'categoryCreated' ) );
				} else {
					notice( 'push' , fetch_notice_output( 'error-occured' ) );
				}
			}
			set_page( 'title' , __( 'Blogster - Create a category' ) );
			
			if(isset($_GET['ajax']))
			{
				$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/ajax_create_cat',$this->data,true,TRUE);
				return array(
					'RETURNED'			=>	$this->data['body'],
					'MCO'				=>	TRUE
				);
			}
			else
			{
				$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/create_cat',$this->data,true,TRUE);
				return $this->data['body'];
			}
		}
		else if($e == 'manage' && $i != null)
		{
			$this->load->library('form_validation');

			if($this->input->post('allower') == 'ALLOWEDITCAT')
			{
				$this->form_validation->set_rules('cat_name', __( 'Category name' ),'required|min_length[3]|max_length[50]');
				$this->form_validation->set_rules('cat_description', __( 'Category Description' ),'required|min_length[3]|max_length[200]');
				if($this->form_validation->run())
				{
					$this->data['notice']	=	$this->news->editCat(
						$this->input->post('cat_id'),
						$this->input->post('cat_name'),
						$this->input->post('cat_description')
					);
					notice('push',fetch_notice_output($this->data['notice']));
				}
			}
			else if($this->input->post('allower') == 'ALLOWCATDELETION')
			{
				$this->form_validation->set_rules('cat_id_for_deletion',__( 'Category id' ),'required|min_length[1]');
				if($this->form_validation->run())
				{
					$this->data['notice']	=	$this->news->deleteCat(
						$this->input->post('cat_id_for_deletion')
					);
					if($this->data['notice']	==	'CatDeleted')
					{
						$this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'category?notice='.$this->data['notice']));
					}
					notice('push',fetch_notice_output($this->data['notice']));
				}
			}
			$this->data['cat']			=	$this->news->retreiveCat($i);
			$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/manage_cat',$this->data,false,TRUE);
		}
	}
	public function delete($se)
	{
		if( !current_user_can( 'delete_posts@blogster' ) )
		{
			$result	= array(
				'status'	=>		'warning',
				'message'	=>		strip_tags(fetch_notice_output('notForYourPriv')),
				'alertType'	=>		'modal',
				'response'	=>		'null'
			);
			return array(
				'RETURNED'	=>	json_encode($result),
				'MCO'		=>	TRUE
			);
		}
		else
		{
			$this->data['delete']		=	$this->news->deleteSpeNews((int)$se);
			$result	= array(
				'status'	=>		'success',
				'message'	=>		'message supprimé',
				'alertType'	=>		'notice',
				'response'	=>		'null'
			);
			return array(
				'RETURNED'	=>	json_encode($result),
				'MCO'		=>	TRUE
			);
		}
	}
	public function comments($page	=	1)
	{
		if( current_user_can( 'blogster_manage_comments@blogster' ) )
		{	
			set_core_vars( 'setting' ,	$this->news->getBlogsterSetting() );
			set_core_vars( 'ttComments' ,	$ttComments	=	$this->news->countComments() );
			set_core_vars( 'paginate', 	$paginate = $this->tendoo->paginate(30,$ttComments,1,'bg-color-red fg-color-white','bg-color-green fg-color-white',$page,$this->url->site_url(array('admin','open','modules',$this->opened_module['namespace'],'comments')).'/') );
			set_core_vars( 'getComments' ,	$this->news->getComments($paginate[1],$paginate[2]) );
			
			set_page('title', __( 'Blogster - Manage Comments' ) );
			
			$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/list_comments',$this->data,false,TRUE);
		}
		else
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
	}
	public function comments_manage($id)
	{
		if( current_user_can( 'blogster_manage_comments@blogster' ) )
		{
			$this->load->library('form_validation');

			if(isset($_POST['approve']))
			{
				$this->form_validation->set_rules('hiddenId','Identifiant du commentaire','trim|required|min_length[1]');
				if($this->form_validation->run())
				{
					if($this->news->approveComment($this->input->post('hiddenId')))
					{
						notice('push',fetch_notice_output('done'));
					}
					else
					{
						notice('push',fetch_notice_output('error-occured'));
					}
				}
			}
			else if(isset($_POST['disapprove']))
			{
				$this->form_validation->set_rules('hiddenId','Identifiant du commentaire','trim|required|min_length[1]');
				if($this->form_validation->run())
				{
					if($this->news->disapproveComment($this->input->post('hiddenId')))
					{
						notice('push',fetch_notice_output('done'));
					}
					else
					{
						notice('push',fetch_notice_output('error-occured'));
					}
				}
			}
			else if(isset($_POST['delete']))
			{
				$this->form_validation->set_rules('hiddenId','Identifiant du commentaire','trim|required|min_length[1]');
				if($this->form_validation->run())
				{
					if($this->news->deleteComment($this->input->post('hiddenId')))
					{
						$this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'comments?notice=commentDeleted'));
					}
				}
			}
			set_core_vars( 'speComment' ,	$this->news->getSpeComment($id) );
			if(!get_core_vars( 'speComment' )): $this->url->redirect(array('admin','open','modules',$this->opened_module['namespace'],'comments?notice=unknowComments'));endif; // redirect if comment doesn't exist.
			set_page('title', __( 'Blogster - Manage Comments' ) );
			
			$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/manage_comments',$this->data,false,TRUE);
		}
		else
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
	}
	public function setting()
	{
		if( current_user_can( 'blogster_setting@blogster' ) )
		{
			if(isset($_POST['update']))
			{
				$this->load->library('form_validation');
				$this->form_validation->set_error_delimiters('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert"><i class="icon-remove"></i></button><i style="font-size:18px;margin-right:5px;" class="icon-warning-sign"></i>', '</div>');

				$this->form_validation->set_rules('validateall','','');
				$this->form_validation->set_rules('allowPublicComment','','');
				$this->form_validation->set_rules('update','','');
				if($this->form_validation->run())
				{
					if($this->news->setBlogsterSetting($this->input->post('validateall'),$this->input->post('allowPublicComment')))
					{
						notice('push',fetch_notice_output('done'));
					}
					else
					{
						notice('push',fetch_notice_output('error-occured'));
					}; // modification des parametres
				}
			}
			if(isset($_FILES['import']))
			{
				$config['file_name']		=	'backup';
				$config['overwrite']		=	TRUE;
				$config['upload_path'] 		= 	$this->link;
				$config['allowed_types'] 	= 	'json';
				$config['remove_spaces']	=	TRUE;
				$this->load->library('upload', $config,null,$this);
				$this->upload->do_upload('import');
				if(is_file($this->link.'backup.json'))
				{
					$content				=	file_get_contents($this->link.'backup.json');
					$fullArray				=	json_decode($content,TRUE);
					$status					=	$this->news->doImport($fullArray);
					notice('push',tendoo_info($status['success'].' requête(s) a/ont correctement été exécutée(s), '.$status['error'].' requête(s) n\'a/ont pas pu être exécutée(s)'));
					unlink($this->link.'backup.json');
				}
			}
			if($this->input->post('export'))
			{
				// Prevent output
				ob_clean();
				$options	=	get_core_vars( 'options' );
				// exportation des données
				header('Content-type: application/octect-stream');
				header('Content-Disposition: attachment; filename="'.$options['site_name'].'_blogster_backup.json"');
				echo $this->news->export();
				die();
			}
			$this->data['setting']		=	$this->news->getBlogsterSetting();
			set_page('title', __( 'Blogster - Settings' ) );
			
			$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/setting',$this->data,false,TRUE);
		}
		else
		{
			$this->url->redirect(array('admin','index?notice=accessDenied'));
		}
	}
	public function tags($action = 'index', $page	=	1)
	{
		if( current_user_can( 'blogster_manage_tags@blogster' ) )
		{
			// Get All keyWords
			$this->data['totalKeyWords']=	count($this->news->getAllPopularKeyWords('all'));
			// Starting Pagination
			$_elPP	=	isset($_GET['limit']) ? $_GET['limit'] : 10;
			$this->data['paginate']		=	pagination_helper(
				$_elPP,
				$this->data['totalKeyWords'],
				$page,
				module_url(array('tags','index'))
			);
			// Get KeyWord Using Page Pagination
			$this->data['getKeywords']	=	$this->news->getAllPopularKeyWords('limitedTo',$this->data['paginate']['start'],$this->data['paginate']['end']);
			// Set Page Title
			set_page('title', __( 'Blogster - Manage Tags' ) );
			
			$this->data['body']			=	$this->load->view(MODULES_DIR.$this->opened_module['encrypted_dir'].'/views/keywords_main',$this->data,true,TRUE,$this);
			return $this->data['body'];
		}
		else
		{
			module_location('?notice=accessDenied');
		}
	}
	public function ajax($section,$params2="",$params3="")
	{
		if($section == 'createCategory')
		{
			$array	=	array(
				'status'	=>		'warning',
				'alertType'	=>		'modal',
				'message'	=>		'La catégoie n\'a pas pu être créer, vérifiez qu\'une catégorie ayant le même nom n\'existe pas déjà.',
				'response'	=>		'cat_creation_error'
			);
			$this->load->library('form_validation');
			$this->form_validation->set_rules('categoryName','Du nom de la cat&eacutegorie','trim|required');
			if($this->form_validation->run())
			{
				$cat	=	$this->data['news']->createCat($this->input->post('categoryName'),'Aucune description Enregistr&eacute;e');
				if($cat)
				{
					$array	=	array(
						'status'	=>		'success',
						'alertType'	=>		'notice',
						'message'	=>		'La catégorie a correctement été créé.',
						'response'	=>		'cat_created',
						'exec'		=>		'function(){
							$(".multiselect").multiselect("destroy");
							$(".multiselect").append("<option value=\"'.$cat[0]['ID'].'\">'.$cat[0]['CATEGORY_NAME'].'</option>")							
							$(".multiselect").multiselect({
								dropRight		: true,
								nonSelectedText	: "Veuillez choisir",
								nSelectedText	:	"cochés",
								enableFiltering	:	true								
							});
						}'
					);
				}
				
			}
			else
			{
				$array	=	array(
					'status'	=>		'warning',
					'alertType'	=>		'modal',
					'message'	=>		'La catégoie n\'a pas pu être créer, vérifiez le nom de la catégorie.',
					'response'	=>		'cat_creation_error'
				);
			}
			return array(
				'MCO'		=>	TRUE,
				'RETURNED'	=>	json_encode($array)
			);
		}
		else if($section == 'tags')
		{
			if($params2 == 'delete')
			{
				// Suppression de mots clés.
				$result	=	$this->news->deleteKeyWords($params3);
				return array(
					'RETURNED'	=>	json_encode($result),
					'MCO'		=>	TRUE
				);
			}
			if($params2 == 'create')
			{
				$this->load->library('form_validation',null,null,$this);
				$this->form_validation->set_rules('kw_title','Champs du titre du mot clé','required|min_length[1]|max_length[30]');
				if($this->form_validation->run())
				{
					$result	=	$this->news->createKeyWord($this->input->post('kw_title'),$this->input->post('kw_description'));
					return array(
						'RETURNED'	=>	json_encode($result),
						'MCO'		=>	TRUE
					);
				}
				else
				{
					return array(
						'RETURNED'	=>	json_encode(array(
							'message'	=>	validation_errors(),
							'alertType'	=>	'modal',
							'status'	=>	'warning',
							'response'	=>	''
						)),
						'MCO'		=>	TRUE
					);
				}
			}
		}
	}
}
