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
											<div class="row">
												<div class="col-sm-8">
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">Nama Menu</label>
														</div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="name" id="name" placeholder="Nama Menu">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">URL</label>
														</div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="href" id="href" placeholder="URL">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">Parent</label>
														</div>
														<div class="col-sm-9">
															<select class="form-control" name="parent_id" id="parent_id">
																<option value="0">(tidak ada)</option>
																<?php foreach($menuParentList->getResult() as $mP) { ?>
																<option value="<?php echo $mP->id; ?>"><?php echo $mP->name; ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">Ikon</label>
														</div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="icon" id="icon" placeholder="Ikon">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">Urutan</label>
														</div>
														<div class="col-sm-9">
															<input type="number" class="form-control" name="order_number" id="order_number" placeholder="Urutan">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3">
															<label class="font-weight-bold">Aktif</label>
														</div>
														<div class="col-sm-9">
															<select class="form-control" name="active" id="active">
																<option value="1">Aktif</option>
																<option value="0">Tidak aktif</option>
															</select>
														</div>
													</div>
													
												</div>
												<div class="col-sm-4 pl-4 pr-4">
													<p class="font-weight-bold">Izin Menu</p>
													<?php foreach($permissionList->getResult() as $pL) { ?>
													
													<input type="checkbox" id="<?php echo $pL->name; ?>" name="<?php echo $pL->name; ?>" value="<?php echo $pL->id; ?>">
													<label for="<?php echo $pL->name; ?>"><?php echo $pL->description; ?></label><br>
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
			required = ["name", "href"];
			errornotice = $("#error");
			emptyerror = "kolom ini harus diisi!";
			
			$('#submit').click(function(){
				//Validate required fields
				for (i=0;i<required.length;i++) {
					var input = $('#'+required[i]);
					if ((input.val() == "") || (input.val() == emptyerror)) {
						input.addClass("needsfilled");
						input.parent().addClass("has-error");
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