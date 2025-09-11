<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link href="assets/css/datatables/responsive.bootstrap.min.css" rel="stylesheet">
	</head>
    <body data-background-color="bg3">
        <div id="wrapper">
			<?php echo view('partials/nav'); ?>

			<div class="main-panel animated fadeIn">
                <div class="content">
					<div class="page-inner">
                        <div class="page-header">
                            <h1 class="page-title"><?php echo $title; ?></h1>
							<ul class="breadcrumbs">
								<li class="nav-home">
									<a href="dashboard">
										<i class="flaticon-home"></i>
									</a>
								</li>
								<?php foreach($breadcrumbs->getResult() as $bc) { ?>
								
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a href="<?php echo $bc->href; ?>"><?php echo $bc->name; ?></a>
								</li>
								<?php } ?>
								
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a href="#"><?php echo $title; ?></a>
								</li>
							</ul>
                        </div>
						<?php if (session()->has('errors')) : ?>
											
						<ul class="alert alert-danger">
							<?php foreach (session('errors') as $error) : ?>
							
							<li><?php echo $error ?></li>
							<?php endforeach ?>
							
						</ul>
						<?php endif ?>
						
						<?php if (session()->has('message')) : ?>
						
						<div class="alert alert-success">
							<h2><?php echo session('message') ?></h2>
						</div>
						<?php endif ?>
						
                        <div class="row">
							<div class="col-md-8">
								<form action="" method="post" enctype="multipart/form-data">
									<div class="card">
										<div class="card-body">
											<div class="mt-3">
												<div class="form-group form-group-default">
													<label class="font-weight-bold">Nama Lengkap</label>
													<input type="text" class="form-control" name="fullname"
														placeholder="Nama Lengkap" value="<?php echo user()->fullname; ?>" required>
												</div>
												<div class="form-group form-group-default">
													<label class="font-weight-bold">Nama Pengguna</label>
													<input type="text" class="form-control" name="username"
														placeholder="Username" value="<?php echo user()->username; ?>" required>
												</div>
												<div class="form-group form-group-default">
													<label class="font-weight-bold">Email</label>
													<input type="email" class="form-control" name="email"
														placeholder="Name" value="<?php echo user()->email; ?>" required>
												</div>
												<button id="changePassword" class="btn btn-warning"><i class="fa fa-key mr-2"></i> Ganti kata sandi</button>
											</div>
											<div class="text-right mt-3 mb-3">
												<button id="back" class="btn btn-danger">Kembali</button>
												<button id="submit" class="btn btn-primary">Simpan</button>
											</div>
										</div>
									</div>
								</form>
							</div>
							<div class="col-md-4">
								<div class="card card-profile">
									<div class="card-header">
										<div class="profile-picture">
											<div class="avatar avatar-xxl">
												<img src="/user_image/<?php echo user()->photo == '' ? 'profile.png' : user()->photo; ?>" alt="..." class="avatar-img rounded-circle">
											</div>
										</div>
									</div>
									<div class="card-body">
										<div class="user-profile text-center">
											<div class="view-profile">
												<form action="/profile/changePhoto" method="post" enctype="multipart/form-data">
													<input type="file" name="file" class="form-control mb-4" required>
													<p class="small">Ukuran berkas maksimal: 100kb<br>Ukuran dimensi maksimal: 128x128</p>
													<button id="change_photo" class="btn btn-primary btn-block">Ganti foto</button>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
                        <!-- /.col-lg-12 -->
                    </div>
                </div>
				<?php echo view('partials/footer'); ?>
				
            </div>
        </div>
		
		<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
		<script src="/assets/js/core/popper.min.js"></script>
		<script src="/assets/js/core/bootstrap.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
		<script src="/assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
		<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
		<script src="/assets/js/ready.min.js"></script>
		
		<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
		
        <script>
			required = ["fullname", "email", "username"];
			errornotice = $("#error");
			emptyerror = "Masukan ini wajib diisi!";
			
			$('#submit').click(function(){
				//Validate required fields
				for (i=0;i<required.length;i++) {
					var input = $('#'+required[i]);
					if ((input.val() == "") || (input.val() == emptyerror)) {
						input.addClass("needsfilled");
						input.parent().addClass("has-error");
						if((input.attr("id") == "password") || (input.attr("id") == "pass_confirm")) {
							input.attr("type", "text");
						}
						input.val(emptyerror);
						errornotice.fadeIn(750);
					} else {
						input.removeClass("needsfilled");
						input.parent().removeClass("has-error");
					}
				}
					
				//if any inputs on the page have the class 'needsfilled' the form will not submit
				if (
					$(":input").hasClass("needsfilled")) {
					return false;
				} else {
					errornotice.hide();
					$('#frm_util').submit();
				}
														  
			});
													
			$("#changePassword").click(function() {
				swal({
					title: "Apakah anda yakin akan mengganti kata sandi?",
					text: "", icon: "warning",
					buttons:{
						cancel: {
							visible: true,
							text : "Cancel", className: "btn"
						},
						confirm: {
							text : "Ganti kata sandi",
							className : "btn btn-warning"
						}
					}
				})
				.then((willChange) => {
					if (willChange) {
						window.location.assign("profile/changePassword");
					}
				});
				return false;
			});
			
			$('#back').click(function(){
				parent.history.back();
				return false;
			});
        </script>
    </body>
</html>