<?php

	class TestTimberHelper extends Timber_UnitTestCase {

		function testPluckArray() {
			$arr = array();
			$arr[] = array('name' => 'Bill', 'number' => 42);
			$arr[] = array('name' => 'Barack', 'number' => 44);
			$arr[] = array('name' => 'Hillary', 'number' => 45);
			$names = \Timber\Helper::pluck($arr, 'name');
			$this->assertEquals(array('Bill', 'Barack', 'Hillary'), $names);
		}

		function testPluckArrayMissing() {
			$arr = array();
			$arr[] = array('name' => 'Bill', 'number' => 42);
			$arr[] = array('name' => 'Barack', 'number' => 44);
			$arr[] = array('name' => 'Hillary', 'number' => 45);
			$arr[] = array('name' => 'Donald');
			$names = \Timber\Helper::pluck($arr, 'number');
			$this->assertEquals(array(42, 44, 45), $names);
		}

		function testPluckObject() {
			$billy = new stdClass();
			$billy->name = 'Billy Corgan';
			$billy->instrument = 'guitar';
			$jimmy = new stdClass();
			$jimmy->name = 'Jimmy Chamberlin';
			$jimmy->instrument = 'drums';
			$pumpkins = array($billy, $jimmy);
			$instruments = \Timber\Helper::pluck($pumpkins, 'instrument');
			$this->assertEquals(array('guitar', 'drums'), $instruments);
		}

		function testPluckObjectWithMethod() {
			require_once(__DIR__.'/php/timber-post-subclass.php');
			$tps = new TimberPostSubclass();
			$jimmy = new stdClass();
			$jimmy->name = 'Jimmy';
			$pumpkins = array($tps, $jimmy);
			$bar = \Timber\Helper::pluck($pumpkins, 'foo');
			$this->assertEquals(array('bar'), $bar);
		}

		function testTrimCharacters() {
			$text    = "Sometimes you need to do such weird things like remove all comments from your project.";
			$trimmed = \Timber\TextHelper::trim_characters( $text, 20 );
			$this->assertEquals( "Sometimes yo&hellip;", $trimmed );
		}

		function testCloseTagsWithSelfClosingTags(){
			$p = '<p>My thing is this <hr>Whatever';
			$html = \Timber\TextHelper::close_tags($p);
			$this->assertEquals('<p>My thing is this <hr />Whatever</p>', $html);
		}

		function testCommentForm() {
			$post_id = $this->factory->post->create();
			$form = Timber\Helper::ob_function( 'comment_form', array( array(), $post_id ) );
			$form = trim($form);
			$this->assertStringStartsWith('<div id="respond"', $form);
		}

		function testWPTitle(){
        	//since we're testing with twentyfourteen -- need to remove its filters on wp_title
        	remove_all_filters('wp_title');
            remove_theme_support( 'title-tag' );
        	$this->assertEquals('', Timber\Helper::get_wp_title());
        }

        function testWPTitleSingle(){
        	//since we're testing with twentyfourteen -- need to remove its filters on wp_title
        	remove_all_filters('wp_title');
        	$post_id = $this->factory->post->create(array('post_title' => 'My New Post'));
        	$post = get_post($post_id);
            $this->go_to( site_url( '?p='.$post_id ) );
        	$this->assertEquals('My New Post', Timber\Helper::get_wp_title());
        }

		function testCloseTags() {
			$str = '<a href="http://wordpress.org">Hi!';
			$closed = Timber\TextHelper::close_tags($str);
			$this->assertEquals($str.'</a>', $closed);
		}

		function testArrayToObject(){
			$arr = array('jared' => 'super cool');
			$obj = Timber\Helper::array_to_object($arr);
			$this->assertEquals('super cool', $obj->jared);
		}

		function testArrayArrayToObject() {
			$arr = array('jared' => 'super cool', 'prefs' => array('food' => 'spicy', 'women' => 'spicier'));
			$obj = Timber\Helper::array_to_object($arr);
			$this->assertEquals('spicy', $obj->prefs->food);
		}

		function testGetObjectIndexByProperty(){
			$obj1 = new stdClass();
			$obj1->name = 'mark';
			$obj1->skill = 'acro yoga';
			$obj2 = new stdClass();
			$obj2->name = 'austin';
			$obj2->skill = 'cooking';
			$arr = array($obj1, $obj2);
			$index = Timber\Helper::get_object_index_by_property($arr, 'skill', 'cooking');
			$this->assertEquals(1, $index);
			$obj = Timber\Helper::get_object_by_property($arr, 'skill', 'cooking');
			$this->assertEquals('austin', $obj->name);
		}

		function testGetObjectByPropertyButNoMatch() {
			$obj1 = new stdClass();
			$obj1->name = 'mark';
			$obj1->skill = 'acro yoga';
			$arr = array($obj1);
			$result = Timber\Helper::get_object_by_property($arr, 'skill', 'cooking');
			$this->assertFalse($result);
		}

		function testGetArrayIndexByProperty(){
			$obj1 = array();
			$obj1['name'] = 'mark';
			$obj1['skill'] = 'acro yoga';
			$obj2 = array();
			$obj2['name'] = 'austin';
			$obj2['skill'] = 'cooking';
			$arr = array($obj1, $obj2);
			$index = \Timber\Helper::get_object_index_by_property($arr, 'skill', 'cooking');
			$this->assertEquals(1, $index);
			$this->assertFalse(\Timber\Helper::get_object_index_by_property('butts', 'skill', 'cooking'));
		}

		/**
     	 * @expectedException InvalidArgumentException
     	 */
		function testGetObjectByPropertyButNo() {
			$obj1 = new stdClass();
			$obj1->name = 'mark';
			$obj1->skill = 'acro yoga';
			$obj = Timber\Helper::get_object_by_property($obj1, 'skill', 'cooking');
		}

		function testTimers() {
			$start = Timber\Helper::start_timer();
			sleep(1);
			$end = Timber\Helper::stop_timer($start);
			$this->assertContains(' seconds.', $end);
			$time = str_replace(' seconds.', '', $end);
			$this->assertGreaterThan(1, $time);
		}

		function testArrayTruncate() {
			$arr = array('Buster', 'GOB', 'Michael', 'Lindsay');
			$arr = Timber\Helper::array_truncate($arr, 2);
			$this->assertContains('Buster', $arr);
			$this->assertEquals(2, count($arr));
			$this->assertFalse(in_array('Lindsay', $arr));
		}

		function testIsTrue() {
			$true = Timber\Helper::is_true('true');
			$this->assertTrue($true);
			$false = Timber\Helper::is_true('false');
			$this->assertFalse($false);
			$estelleGetty = Timber\Helper::is_true('Estelle Getty');
			$this->assertTrue($estelleGetty);
		}

		function testIsEven() {
			$this->assertTrue(Timber\Helper::iseven(2));
			$this->assertFalse(Timber\Helper::iseven(7));
		}

		function testIsOdd() {
			$this->assertFalse(Timber\Helper::isodd(2));
			$this->assertTrue(Timber\Helper::isodd(7));
		}

		function testErrorLog() {
			ob_start();
			$this->assertTrue(Timber\Helper::error_log('foo'));
			$this->assertTrue(Timber\Helper::error_log(array('Dark Helmet', 'Barf')));
			$data = ob_get_flush();
		}

		function testOSort() {
			$michael = new stdClass();
			$michael->name = 'Michael';
			$michael->year = 1981;
			$lauren = new stdClass();
			$lauren->name = 'Lauren';
			$lauren->year = 1984;
			$boo = new stdClass();
			$boo->name = 'Robbie';
			$boo->year = 1989;
			$people = array($lauren, $michael, $boo);
			Timber\Helper::osort($people, 'year');
			$this->assertEquals('Michael', $people[0]->name);
			$this->assertEquals('Lauren', $people[1]->name);
			$this->assertEquals('Robbie', $people[2]->name);
			$this->assertEquals(1984, $people[1]->year);
		}

		function testArrayFilter() {
			$posts = [];
			$posts[] = $this->factory->post->create(array('post_title' => 'Stringer Bell', 'post_content' => 'Idris Elba'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Snoop', 'post_content' => 'Felicia Pearson'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Cheese', 'post_content' => 'Method Man'));
			$posts = Timber::get_posts($posts);
			$template = '{% for post in posts | filter("snoop")%}{{ post.content|striptags }}{% endfor %}';
			$str = Timber::compile_string($template, array('posts' => $posts));
			$this->assertEquals('Felicia Pearson', trim($str));
		}

		function testArrayFilterKeyValueUsingPostQuery() {
			$posts = [];
			$posts[] = $this->factory->post->create(array('post_title' => 'Stringer Bell', 'post_content' => 'Idris Elba'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Snoop', 'post_content' => 'Felicia Pearson'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Cheese', 'post_content' => 'Method Man'));
			$posts = new Timber\PostQuery( array(
				'query' => $posts,
			) );
			$template = '{% for post in posts | filter({post_content: "Method Man"
		})%}{{ post.title }}{% endfor %}';
			$str = Timber::compile_string($template, array('posts' => $posts));
			$this->assertEquals('Cheese', trim($str));
		}

		function testArrayFilterMulti() {
			$posts = [];
			$posts[] = $this->factory->post->create(array('post_title' => 'Stringer Bell', 'post_content' => 'Idris Elba'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Snoop', 'post_content' => 'Felicia Pearson'));
			$posts[] = $this->factory->post->create(array('post_title' => 'Cheese', 'post_content' => 'Method Man'));
			$posts = Timber::get_posts($posts);
			$template = '{% for post in posts | filter({slug:"snoop", post_content:"Idris Elba"}, "OR")%}{{ post.title }} {% endfor %}';
			$str = Timber::compile_string($template, array('posts' => $posts));
			$this->assertEquals('Stringer Bell Snoop', trim($str));
		}

		function testArrayFilterWithBogusArray() {
			$template = '{% for post in posts | filter({slug:"snoop", post_content:"Idris Elba"}, "OR")%}{{ post.title }} {% endfor %}';
			$str = Timber::compile_string($template, array('posts' => 'foobar'));
			$this->assertEquals('', $str);
		}

	}
