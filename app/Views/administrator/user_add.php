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
									<a href="/dashboard">
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
                        <div class="row">
                            <div class="col-md-12">
								<form action="" method="post" enctype="multipart/form-data">
									<div class="card">
										<div class="card-header">
											<button id="back" class="btn btn-danger btn-sm font-weight-bold mb-2">Batal</button>
											<button id="submit" class="btn btn-primary btn-sm font-weight-bold mb-2">Simpan</button>
										</div>
										<div class="card-body">
											<?php if (session()->has('errors')) : ?>
											
											<ul class="alert alert-danger">
												<?php foreach (session('errors') as $error) : ?>
												<li><?php echo $error ?></li>
												<?php endforeach ?>
											</ul>
											<?php endif ?>
											
											<div class="row">
												<div class="col-sm-8">
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Nama Lengkap</label></div>
														<div class="col-sm-9 <?php if (session('errors.fullname')) : ?>has-error<?php endif ?>">
															<input type="text" class="form-control <?php if (session('errors.fullname')) : ?>needsfilled<?php endif ?>"
																name="fullname" id="fullname" placeholder="Nama Lengkap">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold"><?php echo lang('Auth.username'); ?></label></div>
														<div class="col-sm-9 <?php if (session('errors.username')) : ?>has-error<?php endif ?>">
															<input type="text" class="form-control <?php if (session('errors.username')) : ?>needsfilled<?php endif ?>"
																name="username" id="username" placeholder="<?php echo lang('Auth.username'); ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold"><?php echo lang('Auth.email'); ?></label></div>
														<div class="col-sm-9 <?php if (session('errors.email')) : ?>has-error<?php endif ?>">
															<input type="email" class="form-control <?php if (session('errors.email')) : ?>needsfilled<?php endif ?>"
																name="email" id="email" placeholder="<?php echo lang('Auth.email'); ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold"><?php echo lang('Auth.password'); ?></label></div>
														<div class="col-sm-9 <?php if (session('errors.password')) : ?>has-error<?php endif ?>">
															<input type="password" class="form-control <?php if (session('errors.password')) : ?>needsfilled<?php endif ?>"
																name="password" id="password" placeholder="<?php echo lang('Auth.password'); ?>"
																autocomplete="off">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold"><?php echo lang('Auth.repeatPassword'); ?></label></div>
														<div class="col-sm-9 <?php if (session('errors.repeatPassword')) : ?>has-error<?php endif ?>">
															<input type="password" class="form-control <?php if (session('errors.repeatPassword')) : ?>needsfilled<?php endif ?>"
																name="pass_confirm" id="pass_confirm" placeholder="<?php echo lang('Auth.repeatPassword'); ?>"
																autocomplete="off">
														</div>
													</div>
													
												</div>
												
												<div class="col-sm-4 pl-4 pr-4">
													<p class="font-weight-bold">Grup</p>
													<?php foreach($groupList->getResult() as $gL) { ?>
													
													<input type="checkbox" id="<?php echo $gL->name; ?>" name="<?php echo $gL->name; ?>" value="<?php echo $gL->id; ?>">
													<label for="<?php echo $gL->name; ?>"><?php echo $gL->description; ?></label><br>
													<?php } ?>
													
												</div>
												
											</div>
										</div>
									</div>
								</form>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
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
		
        <script>
			required = ["fullname", "email", "username", "password", "pass_confirm"];
			errornotice = $("#error");
			emptyerror = "kolom ini harus diisi!";
			
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
				if ($(":input").hasClass("needsfilled")) {
					return false;
				} else {
					errornotice.hide();
					$('#frm_util').submit();
				}
														  
			});
													
			$(":input").focus(function(){
				if ($(this).hasClass("needsfilled") ) {
					if(($(this).attr("id") == "password") || ($(this).attr("id") == "pass_confirm")) {
						$(this).attr("type", "password");
					}
					$(this).val("");
					$(this).removeClass("needsfilled");
					$(this).parent().removeClass("has-error");
				}
			});
			
			$('#back').click(function(){
				parent.history.back();
				return false;
			});
        </script>
    </body>
</html>