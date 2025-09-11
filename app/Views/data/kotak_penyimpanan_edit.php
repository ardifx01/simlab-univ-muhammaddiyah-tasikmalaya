<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link href="/assets/vendor/select2/select2.css" rel="stylesheet" />
		<link href="/assets/vendor/select2/select2-bootstrap.css" rel="stylesheet" />
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
									<?php echo $lab->nama; ?>
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
														<div class="col-sm-3"><label class="font-weight-bold">Laboraturium</label></div>
														<div class="col-sm-9">
															<select class="form-control" name="id_lab" id="id_lab">
															<?php
															$q = "
																SELECT a.id, a.deskripsi AS nama
																FROM data_lab a
															";
															
															if(!in_groups('admin')){
																$q .= "
																	WHERE IF(
																		EXISTS(
																			SELECT b.id_pengelola
																			FROM data_pengelola b
																			WHERE b.id_lab = a.id
																			AND b.id_pengelola = '".user_id()."'
																		), 1, 0
																	) = 1
																";
															}
															
															$q .= " ORDER BY nama ASC";
															
															$labs = $db->query($q)->getResult();
															
															foreach ($labs as $row)
															{
																echo '<option value="'.$row->id.'" '.($row->id == $lab->id ? 'selected' : '').'>'.$row->nama.'</option>';
															}
															?>
															</select>
														</div>
													</div>
												
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Nama Kotak Penyimpanan</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Kotak Penyimpanan"
															value="<?php echo $KPRow->nama; ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Deskripsi</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="deskripsi" id="deskripsi" placeholder="Deskripsi"
															value="<?php echo $KPRow->deskripsi; ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Awalan Kode</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="code_prefix" id="code_prefix" placeholder="Awalan Kode"
															value="<?php echo $KPRow->code_prefix; ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Akhiran Kode</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="code_suffix" id="code_suffix" placeholder="Akhiran Kode"
															value="<?php echo $KPRow->code_suffix; ?>">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Panjang Kode</label></div>
														<div class="col-sm-9">
															<input type="number" min="0" max="255" step="1" class="form-control" name="code_length" id="code_length"
															placeholder="Panjang Kode" value="<?php echo $KPRow->code_length; ?>">
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
		<script src="/assets/vendor/select2/select2.min.js"></script>
		
        <script>
			required = ["id_lab", "nama", "daftar_barang[]"];
			errornotice = $("#error");
			emptyerror = "";
			
			$(document).ready(function() {
				$('.select2').val([
					<?php
					$barang = $db->query("
						SELECT id_inventaris
						FROM rel_inventaris_kotak_penyimpanan
						WHERE id_kotak_penyimpanan = ".$KPRow->id."
					")->getResult();
					foreach ($barang as $row)
					{
						echo $row->id_inventaris . ',';
					}
					?>
				]);
				$('.select2').select2({
					theme: "bootstrap"
				});
			});
			
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