<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li>
    <a href="{{ backpack_url('dashboard') }}">
        <i class="fa fa-dashboard"></i>
        <span>{{ trans('backpack::base.dashboard') }}</span>
    </a>
</li>
<li>
    <a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/timeentry') }}">
        <i class="fa fa-desktop"></i>
        <span>Time Entry</span>
    </a>
</li>

<!-- Developer Users, Roles Permissions -->
@can('update permissions')
<li class="treeview">
    <a href="#"><i class="fa fa-group"></i> <span>Users, Roles and Permissions</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/user') }}"><i class="fa fa-user"></i> <span>Users</span></a></li>
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/role') }}"><i class="fa fa-group"></i> <span>Roles</span></a></li>
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/permission') }}"><i class="fa fa-key"></i> <span>Permissions</span></a></li>
    </ul>
</li>
<!-- Backups -->
<li><a href="{{ backpack_url('backup') }}"><i class="fa fa-hdd-o"></i> <span>Backups</span></a></li>
<!-- Logs -->
<li><a href="{{ backpack_url('log') }}"><i class="fa fa-terminal"></i> <span>Logs</span></a></li>
@elsecan('update users')
<li class="treeview">
    <a href="#"><i class="fa fa-group"></i> <span>Users</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/user') }}"><i class="fa fa-user"></i> <span>Users</span></a></li>
    </ul>
</li>
@endcan