<?php


class adminPanelController {
	use Block_configuration;
	use Block_register_key;
	use Block_mailer;
	use Block_role;
	use Block_smiley;
	use Block_genres;
	use Block_config_upload;
	use Block_user_list;
	use Block_user_ban_list;
	use Block_team_list;
	use Block_team_approval;
	use Block_team_ban_list;
	use Block_manga_list;
	use Block_manga_trash;


	const GROUP_SYSTEM = 'System';
	const GROUP_USER = 'Users';
	const GROUP_TEAM = 'Team';
	const GROUP_MANGA = 'Manga';

	const BLOCK_CONFIGURATION = 'Configuration';
	const BLOCK_REGISTER_KEY = 'Register-Key';
	const BLOCK_MAILER = 'Mailer';
	const BLOCK_ROLE = 'Role';
	const BLOCK_SMILEY = 'Smiley';
	const BLOCK_GENRES = 'Genres';
	const BLOCK_USER_LIST = 'List';
	const BLOCK_USER_BAN_LIST = 'Ban-List';
	const BLOCK_TEAM_LIST = 'List';
	const BLOCK_TEAM_APPROVAL = 'Approval';
	const BLOCK_TEAM_BAN_LIST = 'Ban-List';
	const BLOCK_CONFIG_UPLOAD = 'Config-Upload';
	const BLOCK_MANGA_LIST = 'List';
	const BLOCK_MANGA_TRASH = 'Trash';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';


	public const INPUT_MAILER_SMTP_AUTHENTICATION = 'smpt_authencation';
	public const INPUT_MAILER_SMTP_USERNAME = 'smpt_username';
	public const INPUT_MAILER_SMTP_PASSWORD = 'smpt_apassword';
	public const INPUT_MAILER_SMTP_HOST = 'smpt_host';
	public const INPUT_MAILER_SMTP_SECURE = 'smpt_secure';
	public const INPUT_MAILER_SMTP_PORT = 'smpt_port';
	public const INPUT_MAILER_API_SERVER = 'api_server';
	public const INPUT_MAILER_API_KEY = 'api_key';
	public const INPUT_MAILER_API_SECRET = 'api_secret';

	public const INPUT_SMILEY_NAME = 'smiley_name';
	public const INPUT_SMILEY_IMAGES = 'smiley_images';

	public const INPUT_ID = 'id';
	public const INPUT_KEY = 'key';
	public const INPUT_QUANTITY = 'quantity';
	public const INPUT_NOTE = 'note';
	public const INPUT_STATUS = 'status';
	public const INPUT_NAME = 'name';
	public const INPUT_TEAM = 'team';
	public const INPUT_TEXT = 'text';
	public const INPUT_COOKIE = 'cookie';
	public const INPUT_COLOR = 'color';
	public const INPUT_LEVEL = 'level';
	public const INPUT_PERMISSION = 'perms';
	public const INPUT_ACTION = '_action';
	public const INPUT_USERNAME = 'username';
	public const INPUT_PASSWORD = 'password';
	public const INPUT_OWN = 'own';
	public const INPUT_CONFIG = 'config';
	public const INPUT_REASON = 'reason';
	public const INPUT_PASSWORD_CONFIRM = 'password_confirm';
	public const INPUT_EMAIL = 'email';
	public const INPUT_ROLE = 'role';
	public const INPUT_ALL = 'all';

	public const ACTION_CHANGE_NAME = 'change_name';
	public const ACTION_CHANGE_USERNAME = 'change_username';
	public const ACTION_CHANGE_PASSWORD = 'change_password';
	public const ACTION_CHANGE_EMAIL = 'change_email';
	public const ACTION_CHANGE_ROLE = 'change_role';
	public const ACTION_CHANGE_OWN = 'change_own';
	public const ACTION_CHANGE_PERMISSION = 'change_permission';
	public const ACTION_BAN = 'ban';
	public const ACTION_UNBAN = 'unban';
	public const ACTION_CHECK_CONFIG = 'check-config';
	public const ACTION_CHANGE_CONFIG = 'change-config';
	public const ACTION_ACCEPT = 'accept';
	public const ACTION_REJECT = 'reject';
	public const ACTION_RESTORE = 'restore';
	public const ACTION_DELETE_FOREVER = 'delete-forever';

	public function index($group, $block = null, $action = null)
	{

		$re_check_permission = true;

		$group_name = null;
		$block_name = null;
		$block_view = null;

		$notification_approval_team = Team::count([
			'active[!]' => Team::IS_ACTIVE
		]);

		switch($group) {
			case self::GROUP_SYSTEM:
			case self::GROUP_USER:
			case self::GROUP_TEAM:
			case self::GROUP_MANGA:
				$group_name = $group;
				break;
			default:
				if(UserPermission::is_access_group_system()) {
					$group_name = self::GROUP_SYSTEM;
				} else if(UserPermission::is_access_group_user()) {
					$group_name = self::GROUP_USER;
				} else if(UserPermission::is_access_group_team()) {
					$group_name = self::GROUP_TEAM;
				} else if(UserPermission::is_access_group_manga()) {
					$group_name = self::GROUP_MANGA;
				}
				break;
		}

		if($group_name == null) {
			return ServerErrorHandler::error_404();
		}

		switch($block) {
			case self::BLOCK_CONFIGURATION:
			case self::BLOCK_REGISTER_KEY:
			case self::BLOCK_MAILER:
			case self::BLOCK_ROLE:
			case self::BLOCK_CONFIG_UPLOAD:
			case self::BLOCK_SMILEY:
			case self::BLOCK_GENRES:
			case self::BLOCK_USER_LIST:
			case self::BLOCK_USER_BAN_LIST:		
			case self::BLOCK_TEAM_LIST:
			case self::BLOCK_TEAM_BAN_LIST:
			case self::BLOCK_TEAM_APPROVAL:
			case self::BLOCK_MANGA_LIST:
			case self::BLOCK_MANGA_TRASH:
				$block_name = $block;
				break;
							
			default:
				switch($group_name) {
					case self::GROUP_SYSTEM:
						if(UserPermission::is_access_configuration()) {
							$block_name = self::BLOCK_CONFIGURATION;
						}
						else if(UserPermission::is_access_register_key()) {
							$block_name = self::BLOCK_REGISTER_KEY;
						}
						else if(UserPermission::is_access_mailer_setting()) {
							$block_name = self::BLOCK_MAILER;
						}
						else if(UserPermission::is_access_role()) {
							$block_name = self::BLOCK_ROLE;
						}
						else if(UserPermission::is_access_config_upload()) {
							$block_name = self::BLOCK_CONFIG_UPLOAD;
						}
						else if(UserPermission::is_access_genres()) {
							$block_name = self::BLOCK_GENRES;
						}
						else if(UserPermission::is_access_smiley()) {
							$block_name = self::BLOCK_SMILEY;
						}

						break;

					case self::GROUP_USER:
						if(UserPermission::is_access_user_list()) {
							$block_name = self::BLOCK_USER_LIST;
						}
						else if(UserPermission::is_access_user_ban_list()) {
							$block_name = self::BLOCK_USER_BAN_LIST;
						}
						break;

					case self::GROUP_TEAM:
						if(UserPermission::is_access_team_list()) {
							$block_name = self::BLOCK_TEAM_LIST;
						}
						else if(UserPermission::is_access_team_approval_list()) {
							$block_name = self::BLOCK_TEAM_APPROVAL;
						}
						else if(UserPermission::is_access_team_ban_list()) {
							$block_name = self::BLOCK_TEAM_BAN_LIST;
						}
						break;

					case self::GROUP_MANGA:
						$block_name = self::BLOCK_MANGA_LIST;
						break;
				}
				$re_check_permission = false;
				break;
		}

		if($block_name == null) {
			return ServerErrorHandler::error_404();
		}

		if($group_name == self::GROUP_SYSTEM) {

			switch($block_name) {
				case self::BLOCK_CONFIGURATION:
					if($re_check_permission && !UserPermission::is_access_configuration()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_configuration($action);
					break;
	
				case self::BLOCK_REGISTER_KEY:
					if($re_check_permission && !UserPermission::is_access_register_key()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_register_key($action);
					break;
	
				case self::BLOCK_MAILER:
					if($re_check_permission && !UserPermission::is_access_mailer_setting()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_mailer($action);
					break;
	
				case self::BLOCK_ROLE:
					if($re_check_permission && !UserPermission::is_access_role()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_role($action);
					break;

				case self::BLOCK_GENRES:
					if($re_check_permission && !UserPermission::is_access_genres()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_genres($action);
					break;

				case self::BLOCK_CONFIG_UPLOAD:
					if($re_check_permission && !UserPermission::is_access_config_upload()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::Block_config_upload($action);
					break;

				case self::BLOCK_SMILEY:
					if($re_check_permission && !UserPermission::is_access_smiley()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_smiley($action);
					break;

			}

		} else if($group_name == self::GROUP_USER) {
			
			switch($block_name) {
				case self::BLOCK_USER_LIST:
					if($re_check_permission && !UserPermission::is_access_user_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_user_list($action);
					break;
	
				case self::BLOCK_USER_BAN_LIST:
					if($re_check_permission && !UserPermission::is_access_user_ban_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_user_ban_list($action);
					break;
			}

		} else if($group_name == self::GROUP_TEAM) {


			switch($block_name) {
				case self::BLOCK_TEAM_LIST:
					if($re_check_permission && !UserPermission::is_access_team_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_team_list($action);
					break;
	
				case self::BLOCK_TEAM_APPROVAL:
					if($re_check_permission && !UserPermission::is_access_team_approval_list()) {
						return ServerErrorHandler::error_404();
					}
					$block_view = self::block_team_approval($action);
					break;

				case self::BLOCK_TEAM_BAN_LIST:
					if($re_check_permission && !UserPermission::is_access_team_ban_list()) {
						return ServerErrorHandler::error_404();
					}

					$block_view = self::block_team_ban_list($action);
					break;
			}

		} else if($group_name == self::GROUP_MANGA) {

			if($re_check_permission && !UserPermission::is_access_group_manga()) {
				return ServerErrorHandler::error_404();
			}

			switch($block_name) {
				case self::BLOCK_MANGA_LIST:
					$block_view = self::block_manga_list($action);
					break;
	
				case self::BLOCK_MANGA_TRASH:
					$block_view = self::block_manga_trash($action);
					break;
			}

		}


		if(!$block_view) {
			return ServerErrorHandler::error_404();
		}

		$title = $block_view['title'].' - Admin Panel'; #edit_lang


		return View::render_theme('admin_panel.index', compact(
			'title',
			'group_name',
			'block_name',
			'block_view',
			'notification_approval_team'
		));
	}
}





?>