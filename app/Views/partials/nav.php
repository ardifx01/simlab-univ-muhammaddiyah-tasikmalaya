<?php
$db = \Config\Database::connect();
$menu = $db->query("
	SELECT a.id, a.name, a.href, a.icon, a.parent_id,
	IF(
		EXISTS(
			SELECT b.permission_id, c.menu_id
			FROM auth_groups_permissions b
			LEFT JOIN menu_permissions c ON(c.permission_id = b.permission_id)
			LEFT JOIN auth_groups_users d ON(b.group_id = d.group_id)
			WHERE d.user_id = ".user_id()."
			AND c.menu_id = a.id
		),
		1, 0
	) AS display,
	IF(
		EXISTS(
			SELECT e.id
			FROM menu e
			WHERE e.parent_id = a.id
		),
		1, 0
	) AS child
	FROM menu a
	WHERE a.active = 1
	ORDER BY COALESCE(a.parent_id, a.id), a.parent_id != 0, a.order_number
");
$groups = $db->query("SELECT b.description FROM auth_groups_users a LEFT JOIN auth_groups b ON(a.group_id = b.id) WHERE a.user_id = ".user_id())->getResult();
?>
			<!-- Navbar -->
			<div class="main-header" data-background-color="dark">
				<!-- Logo Header -->
				<div class="logo-header">
					<a href="/dashboard" class="logo text-white font-weight-bold">
						<img src="/assets/img/logo.webp" alt="navbar brand" width="24" height="24" class="navbar-brand"> SIMLAB UMTAS
					</a>
					<button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon">
							<i class="fa fa-bars"></i>
						</span>
					</button>
					<button class="topbar-toggler more"><i class="fa fa-ellipsis-v"></i></button>
					<div class="navbar-minimize">
						<button class="btn btn-minimize btn-rounded">
							<i class="fa fa-bars"></i>
						</button>
					</div>
				</div>
				<!-- End Logo Header -->
				
				<!-- Navbar Header -->
				<nav class="navbar navbar-header navbar-expand-lg">
					<div class="container-fluid">
						<ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
							<li class="nav-item dropdown hidden-caret">
								<a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
									<div class="avatar-sm">
										<img src="/user_image/<?php echo user()->photo == '' ? 'profile.png' : user()->photo; ?>" alt="..." class="avatar-img rounded-circle">
									</div>
								</a>
								<ul class="dropdown-menu dropdown-user animated fadeIn">
									<li>
										<div class="user-box">
											<div class="avatar-lg">
												<img src="/user_image/<?php echo user()->photo == '' ? 'profile.png' : user()->photo; ?>" alt="..." class="avatar-img rounded">
											</div>
											<div class="u-text">
												<h4><?php echo user()->fullname; ?></h4>
												<p class="text-muted"><?php echo user()->email; ?></p>
												<a href="/profile" class="btn btn-rounded btn-danger btn-sm">Profil Pengguna</a>
											</div>
										</div>
									</li>
									<li>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item" href="/logout">Keluar</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</nav>
			</div>
			<!-- End Navbar -->
			
			<!-- Sidebar -->
			<div class="sidebar">
				<div class="sidebar-wrapper scrollbar-inner">
					<div class="sidebar-content">
						<div class="user">
							<div class="avatar-sm float-left mr-2">
								<img src="/user_image/<?php echo user()->photo == '' ? 'profile.png' : user()->photo; ?>" alt="..." class="avatar-img rounded-circle">
							</div>
							<div class="info">
								<a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
									<span>
										<?php echo user()->fullname; ?>
										<?php foreach ($groups as $user_data){ ?>
										<span class="user-level"><?php echo $user_data->description; ?></span>
										<?php } ?>
										<span class="caret"></span>
									</span>
								</a>
								
								<div class="clearfix"></div>
								
								<div class="collapse in" id="collapseExample">
									<ul class="nav">
										<li>
											<a href="/profile">
												<span class="link-collapse">Profil Pengguna</span>
											</a>
										</li>
										<li>
											<a href="/logout">
												<span class="link-collapse">Keluar</span>
											</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<ul class="nav">
						<?php foreach ($menu->getResult() as $row) { if($row->display == 1 && $row->parent_id == 0) {?>
						
							<li class="nav-item <?php echo (($row->id == $open || $row->id == $active) ? 'active' : ''); ?>">
								<a <?php echo ($row->child == 1 ? 'data-toggle="collapse"' : '') ; ?> href="<?php echo $row->href; ?>">
									<i class="<?php echo $row->icon; ?>"></i>
									<p><?php echo $row->name; ?></p>
									<?php echo ($row->child == 1 ? '<span class="caret"></span>' : '') ; ?>
									
								</a>
								<?php if($row->child == 1) {?>
								
								<div class="collapse <?php echo (($row->id == $open || $row->id == $active) ? 'show' : ''); ?>" id="<?php echo str_replace('#', '', $row->href); ?>">
									<ul class="nav nav-collapse">
										<?php foreach ($menu->getResult() as $row2) { if($row2->display == 1 && $row2->parent_id == $row->id) {?>
										
										<li <?php echo ($row2->id == $active ? 'class="active"' : ''); ?>>
											<a href="<?php echo $row2->href; ?>">
												<span class="sub-item"><?php echo $row2->name; ?></span>
											</a>
										</li>
										<?php }} ?>
										
									</ul>
								</div>
								<?php } ?>
								
							</li>
						<?php }} ?>
						
							<li class="nav-item">
								<a href="/logout">
									<i class="fas fa-sign-out-alt"></i><p>Keluar</p>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>