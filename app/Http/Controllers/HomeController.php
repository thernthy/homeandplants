<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

use Artesaos\SEOTools\Facades\SEOTools;

class HomeController extends Controller
{
	protected $navbar = [];

	public function __construct()
	{
		$this->navbar['nav_country'] = DB::table('creator_teams')
			->select('slug', 'title', 'logo')
			->get();
		
		$this->navbar['foot_keyword'] = DB::table('keywords')
			->select('slug', 'title_en')
			->get();
	
	}

	public function index()
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;
		// --------- seo ------------
		// SEOTools::setTitle('Home');
		// SEOTools::setDescription('This is my page description');
		SEOTools::opengraph()->setUrl('https://cwb-team.net');
		SEOTools::setCanonical('https://cwb-team.net');
		// SEOTools::opengraph()->addProperty('type', 'articles');
		// --------------------------
		$data['keywords'] = DB::table('keywords')
			->select('title_en', 'title_jp', 'slug')
			// ->limit(4)
			->orderBy('id', 'asc')
			->get();

		$data['actions'] = DB::table('actions')
			->select('title_en', 'desc_en', 'img', 'slug')
			// ->limit(4)
			->orderBy('id', 'asc')
			->get();
		// dd($data);
		$data['category'] = DB::table('category')
            ->select('category.img', 'category.title')
			->where('category.status', 1)
            ->limit(6)
			->inRandomOrder()
            ->orderBy('category.id', 'desc')
            ->get();
		$data['main'] = DB::table('main_features')
			->select(
				'main_features.title', 
				'main_features.id', 
				'img_cover',
				'main_features.slug', 
				'img_header', 
				'main_features.created_at', 
			)
			->where('main_features.status', 1)
			->limit(4)
			->orderBy('created_at', 'desc')
			->get();

			$data['activities'] = DB::table('activities')
			->select(
				'activities.title_kh',
				'activities.title_en',
				'activities.photo_cover',
				'activities.slug',
			)
			->where('status', 1)
			->limit(6)
			->get();
		//dd($data['activities']);

		return view('front.home')
		->with('data', $data);
	}

	public function action($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		$data['data'] = DB::table('actions')
			->select('*')
			->where('slug', $slug)
			->first();

		$data['activities'] = DB::table('activities')
			->join(
				'creator_teams',
				'activities.posting_team',
				'=',
				'creator_teams.id'
			)
			->join(
				'action_activities',
				'activities.id',
				'=',
				'action_activities.id_activity'
			)
			->select(
				'activities.title_en',
				'activities.desc_en',
				'activities.photo_cover',
				'activities.slug',
				'creator_teams.logo'
			)
			->where('activities.status', 1)
			->where('action_activities.id_action', $data['data']->id)
			->get();
		
		dd($data['activities']);
		
		// --------- seo ------------
		SEOTools::setTitle($data['data']->title_en);
		// SEOTools::setDescription('This is my page description');
		// --------------------------

		return view('front.action')->with('data', $data);
	}

	public function keyword($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		$data['data'] = DB::table('keywords')
			->select('*')
			->where('slug', $slug)
			->first();
		$data['activities'] = DB::table('activities')
			->join(
				'actions',
				'activities.id_action',
				'=',
				'actions.id'
			)
			->join(
				'creator_teams',
				'activities.posting_team',
				'=',
				'creator_teams.id'
			)
			->join(
				'keyword_activities',
				'activities.id',
				'=',
				'keyword_activities.id_activity'
			)
			->select('activities.*', 'actions.title_en as actionnya', 'actions.slug as action_slug', 'creator_teams.title as country')
			->where('keyword_activities.id_keyword', $data['data']->id)
			->get();

		

		// --------- seo ------------
		// SEOTools::setTitle($data['data']->title);
		// --------------------------

		return view('front.keyword')
		->with('data', $data);
	}

	public function activity($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		$data['data'] = DB::table('activities')
			->join(
				'keyword_activities',
				'activities.id',
				'=',
				'keyword_activities.id_activity'
			)
			->join(
				'keywords',
				'keyword_activities.id_keyword',
				'=',
				'keywords.id'
			)
			->join(
				'creator_teams',
				'activities.posting_team',
				'=',
				'creator_teams.id'
			)
			->join(
				'action_activities',
				'activities.id',
				'=',
				'action_activities.id_activity'
			)
			->join(
				'actions',
				'action_activities.id_action',
				'=',
				'actions.id'
			)
			->select(
				'activities.*', 
				'actions.slug as action_slug',
				'actions.title_en as action_en',
				'creator_teams.title as country_title',
				'creator_teams.slug as country_slug'
			)
			->where('activities.status', 1)
			->where('activities.slug', $slug)
			->first();
		
		$data['keyword'] = DB::table('keywords')
			->join(
				'keyword_activities',
				'keywords.id',
				'=',
				'keyword_activities.id_keyword'
			)
			->select('title_en', 'slug')
			->where('id_activity', $data['data']->id)
			->get();
		
		$data['action'] = DB::table('actions')
			->join(
				'action_activities',
				'actions.id',
				'=',
				'action_activities.id_action'
			)
			->select('title_en', 'slug')
			->where('id_activity', $data['data']->id)
			->get();
		
		// links
		$data['links'] = DB::table('links')
			->select('*')
			->where('id_activity', $data['data']->id)
			->get();

		$data['gallery'] = DB::table('galleries')
            ->select('img', 'title')
            ->limit(3)
			->where('status', 1)
			->where('act_id', $data['data']->id)
			->inRandomOrder()
            ->orderBy('id', 'desc')
            ->get();

		// --------- seo ------------
		SEOTools::setTitle($data['data']->title_en);
		// --------------------------

		return view('front.activity')
		->with('data', $data);
	}

	public function category($slug)
	{
		$data['test'] = 'test';
		// $data['nav_footer'] = $this->navbar;

		// $data['footer_menu'] = DB::table('packages')
		// 	->select('id', 'slug', 'title', 'price')
		// 	->limit(6)
		// 	->orderBy('id', 'asc')
		// 	->get();
			
		// $data['data'] = DB::table('posts')
		// 	->join(
		// 		'categories',
		// 		'posts.cat_id',
		// 		'=',
		// 		'categories.id'
		// 	)
		// 	->select('posts.title', 'posts.id', 'slug', 'img', 'created_at', 'categories.title as cat')
		// 	->orderBy('id', 'desc')
		// 	->get();
		
		// --------- seo ------------
		// SEOTools::setTitle('Blog');
		// --------------------------

		return view('front.category')
		->with('data', $data);
	}

	public function detail($slug)
	{
		$data['test'] = 'test';
		// $data['nav_footer'] = $this->navbar;

		// $data['footer_menu'] = DB::table('packages')
		// 	->select('id', 'slug', 'title', 'price')
		// 	->limit(6)
		// 	->orderBy('id', 'asc')
		// 	->get();
			
		// $data['data'] = DB::table('posts')
		// 	->join(
		// 		'categories',
		// 		'posts.cat_id',
		// 		'=',
		// 		'categories.id'
		// 	)
		// 	->select('posts.title', 'posts.id', 'slug', 'img', 'created_at', 'categories.title as cat')
		// 	->orderBy('id', 'desc')
		// 	->get();
		
		// --------- seo ------------
		// SEOTools::setTitle('Blog');
		// --------------------------

		return view('front.detail')
		->with('data', $data);
	}

	public function products()
	{
		$data['test'] = 'test';
		// $data['nav_footer'] = $this->navbar;

		// $data['footer_menu'] = DB::table('packages')
		// 	->select('id', 'slug', 'title', 'price')
		// 	->limit(6)
		// 	->orderBy('id', 'asc')
		// 	->get();
			
		// $data['data'] = DB::table('posts')
		// 	->join(
		// 		'categories',
		// 		'posts.cat_id',
		// 		'=',
		// 		'categories.id'
		// 	)
		// 	->select('posts.title', 'posts.id', 'slug', 'img', 'created_at', 'categories.title as cat')
		// 	->orderBy('id', 'desc')
		// 	->get();
		
		// --------- seo ------------
		// SEOTools::setTitle('Blog');
		// --------------------------

		return view('front.product')
		->with('data', $data);
	}

	public function story($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;
		
		$data['data'] = DB::table('stories')
			->join(
				'creator_teams',
				'stories.country_id',
				'=',
				'creator_teams.id'
			)
			->join(
				'cms_users',
				'stories.user_id',
				'=',
				'cms_users.id'
			)
			->select('stories.*', 'creator_teams.title as country_title', 'creator_teams.slug as country_slug', 'cms_users.name')
			->where('stories.slug', $slug)
			->first();

		$data['recents'] = DB::table('stories')
			->select('title', 'created_at', 'slug', 'img_header')
			->where('slug', '<>', $slug)
			->limit(3)
			->inRandomOrder()
			->get();

		$data['creator_teams'] = DB::table('creator_teams')
			->select('title', 'id')
			->limit(6)
			->orderBy('id', 'asc')
			->get();

		// --------- seo ------------
		SEOTools::setTitle($data['data']->title);
		// --------------------------

		return view('front.post')
		->with('data', $data);
	}

	public function countries($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		$data['data'] = DB::table('creator_teams')
			->select('*')
			->where('slug', $slug)
			->first();

		$data['activity'] = DB::table('activities')
			->join(
				'creator_teams',
				'activities.posting_team',
				'=',
				'creator_teams.id'
			)
			->select('activities.*', 'creator_teams.title as country')
			->where('activities.status', 1)
			->where('activities.posting_team', $data['data']->id)
			->get();

		$data['sns'] = DB::table('sns')
			->select('*')
			// ->limit(4)
			->orderBy('id', 'asc')
			->where('sns.status', 1)
			->where('country_id', $data['data']->id)
			->get();

		$data['actions'] = DB::table('actions')
			->select('title_en', 'title_jp', 'img', 'slug')
			// ->limit(4)
			->orderBy('id', 'asc')
			->get();

		$data['story'] = DB::table('stories')
			->join(
				'creator_teams',
				'stories.country_id',
				'=',
				'creator_teams.id'
			)
			->join(
				'cms_users',
				'stories.user_id',
				'=',
				'cms_users.id'
			)
			->select(
				'stories.title', 
				'stories.id', 
				'stories.slug', 
				'img_header', 
				'caption_desc', 
				'stories.created_at', 
				'creator_teams.title as country_title',
				'cms_users.name'
			)
			->limit(3)
			->where('stories.status', 1)
			->where('stories.country_id', $data['data']->id)
			->orderBy('id', 'desc')
			->get();

		$data['gallery'] = DB::table('galleries')
			->leftJoin(
				'activities',
				'galleries.act_id',
				'=',
				'activities.id'
			)
			->join(
				'creator_teams',
				'galleries.country_id',
				'=',
				'creator_teams.id'
			)
			->select('galleries.img', 'galleries.title', 'activities.slug')
			->limit(13)
			->where('galleries.status', 1)
			->where('country_id', $data['data']->id)
			->inRandomOrder()
			->orderBy('galleries.id', 'desc')
			->get();

		

		// --------- seo ------------
		// SEOTools::setTitle($data['data']->title);
		// --------------------------

		return view('front.creator_teams')
		->with('data', $data);
	}

	public function apiCountries(){
		$data = array();

		// $survei = DB::table('surveis')
		// ->join(
		//     'pedagangs',
		//     'surveis.pedagang_id',
		//     '=',
		//     'pedagangs.id'
		// )
		// ->join(
		//     'pangans',
		//     'surveis.pangan_id',
		//     '=',
		//     'pangans.id'
		// )
		// ->select(DB::raw('sum('.$request->tipe.') as persediaan'), 'pedagangs.nama')
		// ->where('pangans.id', $request->pangan_id)
		// ->whereBetween('surveis.tgl_input', [$request->tgl_awal, $request->tgl_akhir])
		// ->groupBy('pedagangs.nama')
		// ->get();

		$countries = DB::table('creator_teams')
		->get();

		$data['list'] = $countries;

		// foreach($survei as $item) {
		//     $data['survei'][] = $item->persediaan;
		//     $data['pedagang'][] = $item->nama;
		// }

		return response()->json($data);
	}

	public function mainFeatures($slug)
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		// --------- seo ------------
		SEOTools::setTitle('Main Features');
		// --------------------------

		if($slug=="digital-education-program") {
			return view('front.main-features2')->with('data', $data);
		} else {
			return view('front.main-features')->with('data', $data);
		}
		
	}

	public function communityTourism()
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		// --------- seo ------------
		SEOTools::setTitle('Community Tourism');
		// --------------------------

		return view('front.tourism')
		->with('data', $data);
	}

	public function ticket()
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		// --------- seo ------------
		SEOTools::setTitle('Ticket for Future');
		// --------------------------

		return view('front.ticket')
		->with('data', $data);
	}

	public function contact()
	{
		$data['test'] = 'test';
		$data['nav_footer'] = $this->navbar;

		// --------- seo ------------
		SEOTools::setTitle('Contact Us');
		// --------------------------

		return view('front.contact')->with('data', $data);
	}
}