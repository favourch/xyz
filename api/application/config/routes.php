<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "welcome";
$route['404_override'] = '';
$route['students_in'] = "Students/studentNotIn";
$route['students'] = "Students";
$route['students/(:any)'] = "Students/index/$1";

$route['college/(:num)'] = "college/index/$1";
$route['college'] = "college";

$route['erp/session/(:num)/course_reg']= "erp/dept_cs_reg/$1";
$route['erp/session/(:num)/course_reg_approve']= "erp/dept_cs_reg_approve/$1";
$route['erp/session/(:num)/department_student']= "erp/dept_std/$1";
$route['erp/session/(:num)/department_student_male']= "erp/dept_std_male/$1";
$route['erp/session/(:num)/department_student_female']= "erp/dept_std_female/$1";
$route['erp/session/(:num)/department_staff']= "erp/dept_staff/$1";
$route['erp/session/(:num)/department_pay']= "erp/dept_pay/$1";
$route['erp/college_staff']= "erp/college_staff";
$route['erp/session/(:num)/college_student']= "erp/college_student/$1";
$route['erp/session/(:num)/college_student_male']= "erp/college_student_male/$1";
$route['erp/session/(:num)/college_student_female']= "erp/college_student_male/$1";
$route['erp/session/(:num)/college_course_reg']= "erp/college_cs_reg/$1";
$route['erp/session/(:num)/college_course_reg_approve']= "erp/college_cs_reg_approve/$1";
$route['erp/session/(:num)/college_pay']= "erp/college_pay/$1";
$route['erp/session/(:num)/college_population']= "erp/college_pop/$1";

$route['session/(:num)'] = "session/index/$1";
$route['session'] = "session";

$route['department/(:num)'] = "department/index/$1";
$route['department'] = "department";

$route['programme/(:num)'] = "programme/index/$1";
$route['programme'] = "programme";

$route['news/list'] = "news/index/list";
$route['news/list/(:num)'] = "news/index/list/$1";
$route['news/list/(:num)/(:num)'] = "news/index/list/$1/$2";
$route['news/id/(:num)'] = "news/index/id/$1";

$route['announcements/list'] = "news/announcements/list";
$route['announcements/list/(:num)'] = "news/announcements/list/$1";
$route['announcements/list/(:num)/(:num)'] = "news/announcements/list/$1/$2";
$route['announcements/id/(:num)'] = "news/announcements/id/$1";

$route['cs_reg/(:any)/(:any)'] = "course/getCourseReg/$1/$2";
$route['course'] = "course/index";
$route['course/(:any)'] = "course/index/$1";
$route['lecturer/(:any)'] = "lecturer/getlecturer/$1";
$route['course/registered'] = "course/getregisteredcourses";
$route['course/registered/(:num)'] = "course/getregisteredcourses/$1";
$route['course/info/(:any)'] = "course/getcourseinfo/$1";
$route['course/dept'] = "course/getcoursesfordepartment";
$route['course/dept/(:num)'] = "course/getcoursesfordepartment/$1";
/* End of file routes.php */
/* Location: ./application/config/routes.php */