<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * 
	 */
	class Document extends My_Controller
	{

		public function __construct(){
			parent::__construct();
			
			/*if($this->users->user_validate('9') == false)
			redirect('author/auth/warning');*/
			
			//date_default_timezone_set('Asia/dhaka');
			
		} 

		public function layout($page_name){
	  		$this->load->view('author/layouts/head', $this->data);
	  		$this->load->view('author/layouts/sidebar', $this->data);
	  		$this->load->view('author/layouts/header', $this->data);
			$this->load->view('author/'.$page_name, $this->data);
			$this->load->view('author/layouts/footer');		
		}

		public function index()
		{

			$this->data['title']='Control Panel: Document Manager';
			$this->data['page_name']='Document';
			$this->data['page_icon']='<i class="fa fa-book"></i>';

			$this->data['headline']='';
			$this->data['successmgs']=$this->session->userdata('successmgs');
			$this->session->unset_userdata('successmgs');
						
			$this->load->js[]="public/css/fonts.css";
			$this->load->js[]="public/js/default.js";
			$this->load->js[]="public/js/checkall.js";
			$this->load->js[]="public/js/sorttable.js";
			$this->load->js[]="third_party/facebox/facebox.js";
			$this->load->css[]="third_party/facebox/facebox.css";		
			$this->load->css[]="maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.css";		

			$filter=$this->input->post();
			if(isset($filter['filter_text']))
			$filter_text=$filter['filter_text'];

			if($_POST){
				$this->db->order_by('doc_id');
				// $this->db->like('grp_id',$filter_text);
				$this->db->select('*');	
				$q=$this->db->get('tbl_document');
				$this->load->group=$q->result_array();
									
				$data['pages_links']=' ';	

		    }else{

				$config['base_url'] = base_url().'author/document/index';
				$config['per_page'] = 20;
				//$config['display_pages']=FALSE;
				$config['num_links'] =5;
				$config['uri_segment'] = 4;
				$config['next_link'] = 'Next';
				$config['prev_link'] = 'Previous';
				//$config['use_page_numbers'] = FALSE;
				//$config['page_query_string'] = FALSE;
				$config['last_link'] = 'Last';
				$config['first_link'] = 'First';
				$config['cur_tag_open'] = '<span class=current>';
				$config['cur_tag_close'] = '</span>';
				//$config['next_link'] = '&gt;';

				$limit=filter_var(trim($this->uri->segment(4, 0)), FILTER_VALIDATE_INT);
				
				$this->db->order_by('doc_id');
				$this->db->limit($config['per_page'], $limit); 	
				$this->db->select('*');	
				$q1=$this->db->get('tbl_document');
				$this->load->document=$q1->result_array();	

				// $this->db->select('*');
				// $value=$this->db->get('tbl_document');
				// $this->load->group_info=$value;								
							 
				// $config['total_rows'] = $this->db->count_all_results('tbl_document');
				
				$this->pagination->initialize($config);
				$this->data['pages_links']=$this->pagination->create_links();
			}
					
			$this->layout('document/index');
		}
		
		public function add(){

			$this->data['title']='Document Manager &rarr; Add New Document';
			$this->data['page_name']='entry/add';
			$this->data['parent_page']='entry';
			$this->data['page_icon']='<i class="fa fa-book"></i>';		
			$this->data['headline']='';		
			$this->data['date']=date("Y-m-d");
			
			$this->load->css[]="public/css/jquery-ui.min.css";
			$this->load->js[]="public/js/defaultjs.js";
			$this->load->js[]="public/js/jquery-ui.min.js";		

			$filter=$this->input->post();
			if(isset($filter['filter_text']))
			$filter_text=$filter['filter_text'];

			$this->db->select('*')->order_by('doc_id');
			$value=$this->db->get('tbl_document');
			$this->load->project_name=$value;

			// echo "<pre>";
			// print_r($value->row());
			// exit();


			$this->load->library('form_validation');
			$this->form_validation->set_error_delimiters('<span class="errormgs" style="color:red;">', '</span>');

			if (isset($_POST["create_folder"])) {

				$this->form_validation->set_rules('company_name', 'Company Name', 'required');
				$this->form_validation->set_rules('project_name', 'Project Name', 'required');
				$this->form_validation->set_rules('year', 'Year', 'required');
				$this->form_validation->set_rules('month', 'Month', 'required');
				$this->form_validation->set_rules('type', 'Type', 'required');
				$this->form_validation->set_rules('filename', 'File Name', 'required');
				$this->form_validation->set_rules('editor1', 'Body', 'required');
				$this->form_validation->set_rules('type', 'Status', 'required');
			
				if($this->form_validation->run() == FALSE){
					$this->layout('document/add');
				}else{

					foreach($this->input->post() as $key=>$value){
						 $$key=$value;
					}
					
					$sql_data=array(
						'doc_company' => $company_name,
						'doc_project_name'=>$project_name, 
						'doc_year'=>$year, 
						'doc_month'=>$month, 
						'doc_type'=>$type, 
						'doc_filename'=>$filename, 
						'doc_body'=>$editor1, 
						'doc_status'=>$type, 
						'insert_date'=>date('Y-m-d'),
						// 'grp_parent'=>$grp_parent,
					);

					// echo "<pre>";
					// print_r($sql_data);
					// exit();

					$insert=$this->db->insert('tbl_document', $sql_data);

					// Create File/Folder


					$this->load->library('Pdf');

					$filename = $filename.'.txt';
					$phpName = $filename.'.php';

					$newFileContent = $this->content($editor1); 

					// if (file_put_contents($newFileName, $newFileContent) !== false) {
					//     echo "File created (" . basename($newFileName) . ")";
					// } else {
					//     echo "Cannot create file (" . basename($newFileName) . ")";
					// }


					$folder = './Documents/'.$company_name.'/'.$project_name.'/'.$year.'/'.$month.'/'.$type;

					$filenam = $folder.'/'.$filename;

					if (!is_dir($folder)){ 
						mkdir($folder,0777, true);
					}

					elseif (!file_put_contents($filename, $newFileContent)) {
						// fopen($filename);
						echo basename($filename.".php");

						echo "string";exit();

						
					}
					
					if($insert){
						$this->session->set_userdata('successmgs', 'Record successfullly added.');
						redirect('author/document/'.$this->uri->segment(5));	 
					}else{
					 	$this->data['message']="Records not added. There is problem";
						$this->layout('document/add');
					}
				}

			}

			elseif(isset($_POST["submit"])){
				echo "hi";
				exit();
				$this->form_validation->set_rules('company_name', 'Company Name', 'required');
				$this->form_validation->set_rules('project_name', 'Project Name', 'required');
				$this->form_validation->set_rules('year', 'Year', 'required');
				$this->form_validation->set_rules('month', 'Month', 'required');
				$this->form_validation->set_rules('type', 'Type', 'required');
				$this->form_validation->set_rules('filename', 'File Name', 'required');
				$this->form_validation->set_rules('editor1', 'Body', 'required');
				$this->form_validation->set_rules('status', 'Status', 'required');
			
				if($this->form_validation->run() == FALSE){
					$this->layout('document/add');
				}else{

					foreach($this->input->post() as $key=>$value){
						 $$key=$value;
					}
					
					$sql_data=array(
						'doc_company' => $company_name,
						'doc_project_name'=>$project_name, 
						'doc_year'=>$year, 
						'doc_month'=>$month, 
						'doc_type'=>$type, 
						'doc_filename'=>$filename, 
						'doc_body'=>$editor1, 
						'doc_status'=>$status, 
						'insert_date'=>date('Y-m-d'),
						// 'grp_parent'=>$grp_parent,
					);

					// echo "<pre>";
					// print_r($sql_data);
					// exit();

					$insert=$this->db->insert('tbl_document', $sql_data);
					
					if($insert){
						$this->session->set_userdata('successmgs', 'Record successfullly added.');
						redirect('author/document/'.$this->uri->segment(5));	 
					}else{
					 	$this->data['message']="Records not added. There is problem";
						$this->layout('document/add');
					}
				}
			}

			else{
				$this->layout('document/add');
			}

			
			

		}







		public function content($content){
			return $content;
		}












		public function view($id=""){

			if(!isset($id) || empty($id) ){
				show_404();
			}
				
			$this->data['title']='Document Manager &rarr; View Document';
			$this->data['page_name']='document/view';
			$this->data['parent_page']='document';
			$this->data['page_icon']='<i class="fa fa-book"></i>';		
			$this->data['headline']='';

			$this->load->css[]="public/css/jquery-ui.min.css";
			$this->load->js[]="public/js/defaultjs.js";
			$this->load->js[]="public/js/jquery-ui.min.js";

			$this->db->select('*')->where('doc_id',$id);
			$value=$this->db->get('tbl_document');
			$this->load->document=$value->row();

			// echo "<pre>";
			// print_r($value); exit();

			$this->layout('document/view');
		}

	}
?>