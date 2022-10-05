@extends('layouts/admin/default')

@section('title')
    @lang('admin.team_members')
@stop

@section('header_styles')

@stop

@section('content')

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif      
        <div class="header-title">
            <h1 class="title">@lang('admin.team_members')</h1>            
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','user','list')!!}
                </ul>
            </div>
            <div class="team-title">
                <h3>@lang('admin.manage_users')</h3>
                <p>@lang('admin.add_your_team_members_and_manage_thier_details_and_user_permission').</p>
            </div>

            <div class="team-mem-wrap">
                @if($permission_arr['add'] === true)
                    <div class="col-userBox text-center">
                        <a href="{{ action('Admin\User\AdminController@create') }}" class="add-user-icon">
                           <span class="add-icon icon-add5"></span>
                           <span class="btn-grey btn-primary">@lang('admin.add_new_member')</span>
                        </a>
                    </div>
                @endif
                @foreach ($admin_lists as $key => $admin_list)
                    @if($admin_list->admin_level != -1 || Auth::guard('admin_user')->user()->admin_level == -1)
                        <div class="col-userBox">
                            <div class="edit-user">
                                <span class="roleName">{{ $admin_list->role['name'] }}</span>
                                @if($admin_list->admin_level == -1) 
                                    <span class="icon-royal-crown-svgrepo-com close"></span>
                                @elseif($permission_arr['delete'] === true)
                                    <a href="{{ action('Admin\User\AdminController@delete', $admin_list->id) }}" onclick="return confirm('@lang('admin.are_you_sure_to_delete_this_user')');">
                                        <span class="icon-close close"> </span>
                                    </a>
                                @endif
                            </div>
                            
                            <div class="muser">
                               @if(isset($profile_image[$admin_list->id]))
                                  <img src="{{ getUserImageUrl($profile_image[$admin_list->id]) }}" alt="{{ $admin_list->nick_name }}" title="{{ $admin_list->nick_name }}" width="136" height="136" class="img-circle">
                               @else
                                  <img src="{{ getUserImageUrl('') }}" alt="{{ $admin_list->nick_name }}" title="{{ $admin_list->nick_name }}" width="136" height="136" class="img-circle">

                               @endif


                            </div>
                            <span class="title-name">{{ $admin_list->nick_name }}</span>
                            <span class="name">{{ $admin_list->first_name.' '.$admin_list->last_name }}</span>
                            <a href="mailto:manisara@gmail.com" class="email">{{ $admin_list->email }}</a>
                            <a href="tel:081-331-1253" class="tel">{{ $admin_list->contact_no }}</a>
                            <div class="create">
                                <div class="create-row">
                                    @lang('common.created'): {{ getDateFormat($admin_list->created_at, '6') }}
                                </div>
                                <div class="create-row">
                                    @lang('common.last_updated'): {{ getDateFormat($admin_list->updated_at, '6') }}
                                </div>
                                <div class="create-row">
                                    @lang('admin.last_login') : <span class="skyblue"> {{ (strtotime($admin_list->last_login) > 0)?getDateFormat($admin_list->last_login, '6'):'---' }}</span>
                                </div>
                                
                                <a class="btn-grey btn-primary" href="{{ action('Admin\User\AdminController@edit', $admin_list->id) }}">@lang('admin.view_infomation')</a>
                            </div>
                        </div>
                    @endif
                @endforeach    
            </div>    
        </div>
    </div>       
@stop

@section('footer_scripts') 
    
@stop
