<?php

class UserPermission {
    private const IS_ADMIN = 98;
    public const ALL_PERMS = '*';

    private static $permissions = [
        'admin_configuration' => 'Cài đặt hệ thống',
        'admin_register_key_create' => 'Tạo mã đăng kí',
        'admin_register_key_delete' => 'Xoá mã đăng kí',
        'admin_mailer_setting' => 'Cấu hình Mailer',
        'admin_role_create' => 'Thêm vai trò người dùng',
        'admin_role_edit' => 'Chỉnh sửa vai trò người dùng',
        'admin_role_delete' => 'Xoá vai trò người dùng',
        'admin_smiley_create' => 'Thêm nhãn dán',
        'admin_smiley_edit' => 'Chỉnh sửa nhãn dán',
        'admin_smiley_delete' => 'Xoá nhãn dán',
        'admin_genres_create' => 'Thêm thể loại',
        'admin_genres_edit' => 'Chỉnh sửa thể loại',
        'admin_genres_delete' => 'Xoá thể loại',
        
        'admin_user_edit' => 'Chỉnh sửa thông tin thành viên',
        'admin_user_ban' => 'Cấm thành viên',
        'admin_user_unban' => 'Bỏ cấm thành viên',

        'admin_team_edit' => 'Chỉnh sửa thông tin nhóm',
        'admin_team_ban' => 'Cấm nhóm',
        'admin_team_unban' => 'Bỏ cấm nhóm',
        'admin_team_approval' => 'Xét duyệt nhóm',

        'admin_config_upload_create' => 'Tạo Config Upload',
        'admin_config_upload_edit' => 'Chỉnh sửa Config Upload',
        'admin_config_upload_delete' => 'Xoá Config Upload',

        'admin_manga_edit' => 'Chỉnh sửa truyện',
        'admin_manga_delete' => 'Xoá truyện',

        'admin_delete_comment' => 'Xoá bình luận',

        'user_comment' => 'Viết bình luận',
        'user_follow' => 'Theo dỗi truyện',
        'user_history' => 'Truyện gần đây',

        'tool_leech' => 'Cho phép sử dụng toolleech'
    ];

    public static function member_default() { // quyền mặc định khi người dùng đăng kí tài khoản mới
        return [
            'user_comment',
            'user_follow',
            'user_history'
        ];
    }

    private static function get_user($user_id = null) {
        return $user_id == "" ? Auth::$data : (is_array($user_id) ? $user_id : User::get($user_id));
    }

    public static function isAdmin($user_id = null) {
        $user = self::get_user($user_id);

        if(!$user) {
            return false;
        }

        return $user['adm'] == self::IS_ADMIN ? true : false;
    }

    public static function list() {
        return self::$permissions;
    }

    public static function has($permission, $user_id = null) {
        $user = self::get_user($user_id);
        if(!$user)
        {
            return false;
        }

        if(self::isAdmin($user))
        {
            return true;
        }

        $permissions = self::get($user);

        if(!is_array($permission))
        {
            $permission = [$permission];
        }

        foreach($permission as $perm) {
            if(array_key_exists($perm, self::$permissions) && in_array($perm, $permissions)) {
                return true;
            }            
        }

        return false;
    }

    public static function get($user_id = null) {
        $user = self::get_user($user_id);
        if(!$user)
        {
            return [];
        }

        if($user['id'] === Auth::$data['id'] && is_array(Auth::$permissions)) {
            return Auth::$permissions;
        }

        
        if(self::isAdmin($user))
        {
            return array_keys(self::$permissions);
        }



        $permissions_role = isset($user['role_perms']) ? json_decode($user['role_perms'], true) : [];


        if(in_array(self::ALL_PERMS, $permissions_role)) {
            return array_keys(self::$permissions);
        }


        $permissions = [];
		if($permissions_role)
		{
			try {
				$permissions = $permissions_role;
				if(!is_array($permissions))
				{
					$permissions = [];
				}
			} catch(Error $error){
				$permissions = [];
			}
		}

		if($user['perms'])
		{

			$permissions_user = json_decode($user['perms'], true);

			foreach ($permissions_user as $key => $value)
			{
				$check = array_search($key, $permissions);
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				if($check === false)
				{
					if($value == true)
					{
						$permissions[] = $key;
					}
				} else
				{
					if($value != true && isset($permissions[$check]))
					{
						unset($permissions[$check]);
					}
				}
			}
		}

        $permissions = array_values($permissions);

        if($user['id'] === Auth::$data['id'] && Auth::$permissions === null) {
            Auth::$permissions = $permissions;
        }

        return $permissions;
    }


    public static function is_access_configuration($user_id = null) {
        return self::has([
            'admin_configuration'
        ], $user_id);
    }

    public static function is_access_register_key($user_id = null) {
        return self::has([
            'admin_register_key_create',
            'admin_register_key_delete'
        ], $user_id);
    }

    public static function is_access_mailer_setting($user_id = null) {
        return self::has([
            'admin_mailer_setting'
        ], $user_id);
    }

    public static function is_access_role($user_id = null) {
        return self::has([
            'admin_role_create',
            'admin_role_edit',
            'admin_role_delete'
        ], $user_id);
    }

    public static function is_access_smiley($user_id = null) {
        return self::has([
            'admin_smiley_create',
            'admin_smiley_edit',
            'admin_smiley_delete'
        ], $user_id);
    }

    public static function is_access_genres($user_id = null) {
        return self::has([
            'admin_genres_create',
            'admin_genres_edit',
            'admin_genres_delete'
        ], $user_id);
    }

    public static function is_access_user_list($user_id = null) {
        return self::has([
            'admin_user_edit',
            'admin_user_ban'
        ], $user_id);
    }

    public static function is_access_user_ban_list($user_id = null) {
        return self::has([
            'admin_user_unban'
        ], $user_id);
    }

    public static function is_access_team_list($user_id = null) {
        return self::has([
            'admin_team_edit',
            'admin_team_ban'
        ], $user_id);
    }

    public static function is_access_team_approval_list($user_id = null) {
        return self::has([
            'admin_team_approval'
        ], $user_id);
    }

    public static function is_access_team_ban_list($user_id = null) {
        return self::has([
            'admin_team_unban'
        ], $user_id);
    }

    public static function is_access_config_upload($user_id = null) {
        return self::has([
            'admin_config_upload_create',
            'admin_config_upload_edit',
            'admin_config_upload_delete'
        ], $user_id);
    }

    public static function is_access_delete_comment($user_id = null) {
        return self::has([
            'admin_delete_comment'
        ], $user_id);
    }

    public static function is_access_group_system($user_id = null) {
        return self::has([
            'admin_configuration',
            'admin_register_key_create',
            'admin_register_key_delete',
            'admin_mailer_setting',
            'admin_role_create',
            'admin_role_edit',
            'admin_role_delete',
            'admin_smiley_create',
            'admin_smiley_edit',
            'admin_smiley_delete',
            'admin_genres_create',
            'admin_genres_edit',
            'admin_genres_delete',
            'admin_config_upload_create',
            'admin_config_upload_edit',
            'admin_config_upload_delete'
        ], $user_id);
    }
   
    public static function is_access_group_user($user_id = null) {
        return self::has([
            'admin_user_edit',
            'admin_user_ban',
            'admin_user_unban'
        ], $user_id);
    }

    public static function is_access_group_team($user_id = null) {
        return self::has([
            'admin_team_edit',
            'admin_team_ban',
            'admin_team_unban',
            'admin_team_approval'
        ], $user_id);
    }
    
    public static function is_access_group_manga($user_id = null) {
        return self::has([
            'admin_manga_edit',
            'admin_manga_delete'
        ], $user_id);
    }

    public static function isAccessAdminPanel($user_id = null) {
        $user = self::get_user($user_id);

        if(self::is_access_group_system($user) == true) {
            return true;
        }
        if(self::is_access_group_user($user) == true) {
            return true;
        }
        if(self::is_access_group_team($user) == true) {
            return true;
        }
        if(self::is_access_group_manga($user) == true) {
            return true;
        }
        return false;
    }

}

?>