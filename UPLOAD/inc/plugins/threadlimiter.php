<?php

/*
ThreadLimiter Plugin for MyBB
Copyright (C) 2014 SvePu

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("newthread_start", "threadlimiter_addnewthread");
$plugins->add_hook("forumdisplay_get_threads", "threadlimiter_newthreadbutton");

function threadlimiter_info()
{
	global $plugins_cache, $mybb, $db, $lang;
	$lang->load('config_threadlimiter');
	$info = array
	(
		"name"			=>	$db->escape_string($lang->threadlimiter),
		"description"	=>	$db->escape_string($lang->threadlimiter_desc),
		"website"		=>	"https://github.com/SvePu/ThreadLimiter",
		"author"		=>	"SvePu",
		"authorsite"	=> 	"https://github.com/SvePu",
		"codename"		=> "threadlimiter",
		"version"		=>	"1.2",
		"compatibility"	=>	"18*"
	);
	$info_desc = '';
	$gid_result = $db->simple_select('settinggroups', 'gid', "name = 'threadlimiter_settings'", array('limit' => 1));
	$settings_group = $db->fetch_array($gid_result);
	if(!empty($settings_group['gid']))
	{
		$info_desc .= "<span style=\"font-size: 0.9em;\">(~<a href=\"index.php?module=config-settings&action=change&gid=".$settings_group['gid']."\"> ".$db->escape_string($lang->threadlimiter_settings_title)." </a>~)</span>";
	}
    
    	if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['threadlimiter'])
    	{
		$info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank" />
		<input type="hidden" name="cmd" value="_s-xclick" />
		<input type="hidden" name="hosted_button_id" value="VGQ4ZDT8M7WS2" />
		<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
		<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
		</form>';
	}
	if($info_desc != '')
	{
		$info['description'] = $info_desc.'<br />'.$info['description'];
	}
    	return $info;
}

function threadlimiter_activate()
{
    	global $db, $lang;	
	$result = $db->simple_select('settinggroups', 'gid', "name = 'threadlimiter_settings'", array('limit' => 1));
	$group = $db->fetch_array($result);

	if(empty($group['gid']))
	{
		$lang->load('config_threadlimiter');
		$query_add = $db->simple_select("settinggroups", "COUNT(*)");
		$rows = $db->fetch_field($query_add, "rows");
		$threadlimiter_group = array(
		"name" 			=>	"threadlimiter_settings",
		"title" 		=>	$db->escape_string($lang->threadlimiter_settings_title),
		"description" 	=>	$db->escape_string($lang->threadlimiter_settings_title_desc),
		"disporder"		=> 	$rows+1,
		"isdefault" 	=>  0
		);
    		$db->insert_query("settinggroups", $threadlimiter_group);
		$gid = $db->insert_id();
	
		$threadlimiter_1 = array(
        	'name'			=> 'threadlimiter_enable',
        	'title'			=> $db->escape_string($lang->threadlimiter_enable_title),
        	'description'  	=> $db->escape_string($lang->threadlimiter_enable_title_desc),
        	'optionscode'  	=> 'yesno',
        	'value'        	=> '1',
        	'disporder'		=> 1,
        	"gid" 			=> (int)$gid
    		);
		$db->insert_query('settings', $threadlimiter_1);
	
		$threadlimiter_2 = array(
		"name"			=> "threadlimiter_gid",
		"title"			=> $db->escape_string($lang->threadlimiter_gid_title),
		"description" 	=> $db->escape_string($lang->threadlimiter_gid_title_desc),
        	'optionscode'  	=> 'groupselect',
        	'value'        	=> '2,5',
		"disporder"		=> "2",
		"gid" 			=> (int)$gid
		);
		$db->insert_query("settings", $threadlimiter_2);
	
		$threadlimiter_3 = array(
		"name"			=> "threadlimiter_limit",
		"title"			=> $db->escape_string($lang->threadlimiter_limit_title),
		"description" 	=> $db->escape_string($lang->threadlimiter_limit_title_desc),
        	'optionscode'  	=> 'numeric',
        	'value'        	=> '2',
		"disporder"		=> "3",
		"gid" 			=> (int)$gid
		);
		$db->insert_query("settings", $threadlimiter_3);
	
		$threadlimiter_4 = array(
		"name"			=> "threadlimiter_reset_time",
		"title"			=> $db->escape_string($lang->threadlimiter_reset_time_title),
		"description" 	=> $db->escape_string($lang->threadlimiter_reset_time_title_desc),
        	'optionscode'  	=> 'numeric',
        	'value'        	=> '0',
		"disporder"		=> "4",
		"gid" 			=> (int)$gid
		);
		$db->insert_query("settings", $threadlimiter_4);
	
	
    		$threadlimiter_5 = array(
		"name"			=> "threadlimiter_fid",
		"title"			=> $db->escape_string($lang->threadlimiter_fid_title),
		"description" 	=> $db->escape_string($lang->threadlimiter_fid_title_desc),
        	'optionscode'  	=> 'forumselect',
        	'value'        	=> '-1',
		"disporder"		=> "5",
		"gid" 			=> (int)$gid
		);
		$db->insert_query("settings", $threadlimiter_5);
		rebuild_settings();
	}
}

function threadlimiter_deactivate()
{
	global $mybb, $db;	
	
	$result = $db->simple_select('settinggroups', 'gid', "name = 'threadlimiter_settings'", array('limit' => 1));
	$group = $db->fetch_array($result);
	
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		rebuild_settings();
	}
}

function threadlimiter_addnewthread()
{
	global $fid, $mybb, $db, $settings, $lang;
	$lang->load('threadlimiter');
	if(!$fid || $settings['threadlimiter_fid'] == '' || !$mybb->user['uid'] || !is_member($settings['threadlimiter_gid']))
	{
		return;
	}
	if(in_array($fid, explode(",", $settings['threadlimiter_fid'])) || $settings['threadlimiter_fid'] == "-1")
	{
		$resettimer = '';
		if($settings['threadlimiter_reset_time'] > 0)
		{
			$timesearch = TIME_NOW - (60 * 60 * 24 * $settings['threadlimiter_reset_time']);
			$resettimer = ' AND dateline>'.$timesearch.'';
		}
		$query = $db->simple_select("threads", "*", "uid='{$mybb->user['uid']}' AND fid='{$fid}'{$resettimer}");
		$numthreads = $db->num_rows($query);    
		if($numthreads >= $settings['threadlimiter_limit'])
		{
			if ($settings['threadlimiter_limit'] == "1")
			{
				error($lang->sprintf($db->escape_string($lang->threadlimiter_error_one), $settings['threadlimiter_limit']));
			}
			else
			{
				error($lang->sprintf($db->escape_string($lang->threadlimiter_error_more), $settings['threadlimiter_limit']));
			}
		} 
	}        
}  

function threadlimiter_newthreadbutton()
{
	global $fid, $mybb, $db, $settings, $newthread, $hide_button;
	if(!$fid || $settings['threadlimiter_fid'] == '' || !$mybb->user['uid'] || !is_member($settings['threadlimiter_gid']))
	{
		return;
	}   
	$hide_button = false;

	if(!$hide_button)
	{
		if(in_array($fid, explode(",", $settings['threadlimiter_fid'])) || $settings['threadlimiter_fid'] == "-1")
		{
			$resettimer = '';
			if($settings['threadlimiter_reset_time'] > 0)
			{
				$timesearch = TIME_NOW - (60 * 60 * 24 * $settings['threadlimiter_reset_time']);
				$resettimer = ' AND dateline>'.$timesearch.'';
			}
			$query = $db->simple_select("threads", "*", "uid='{$mybb->user['uid']}' AND fid='{$fid}'{$resettimer}");
			$numthreads = $db->num_rows($query);    
			if($numthreads >= $settings['threadlimiter_limit'])
			{
				$hide_button = true;
				$newthread = "";
			}  
		}  
	}   
}
