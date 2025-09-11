<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link name="/assets/css/datatables/responsive.bootstrap.min.css" rel="stylesheet">
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
									<a name="/dashboard">
										<i class="flaticon-home"></i>
									</a>
								</li>
								<?php foreach($breadcrumbs->getResult() as $bc) { ?>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a name="<?php echo $bc->name; ?>"><?php echo $bc->name; ?></a>
								</li>
								<?php } ?>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<?php echo $nama_lab; ?>
								</li>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a name="#"><?php echo $title; ?></a>
								</li>
							</ul>
                        </div>
                        <?php if (session()->has('message')) : ?>
						
						<div class="alert alert-success">
							<h2><?php echo session('message') ?></h2>
						</div>
						<?php endif ?>
						<?php if (session()->has('error')) : ?>
						
						<div class="alert alert-danger">
							<h2><?php echo session('error') ?></h2>
						</div>
						<?php endif ?>
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
												<div class="col-sm-12">
												
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Nama Ruangan</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Ruangan">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Deskripsi</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="deskripsi" id="deskripsi" placeholder="Deskripsi">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Lokasi Lantai</label></div>
														<div class="col-sm-9">
															<select class="form-control" name="lantai" id="lantai">
															<?php
															for ($x = $roomRow->lantai_awal; $x <= ($roomRow->lantai_awal + ($roomRow->jml_lantai - 1)); $x++)
															{
																echo '<option value="'.$x.'">Lantai '.$x.'</option>';
															}
															?>
															</select>
														</div>
													</div>
													
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
			required = ["nama"];
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