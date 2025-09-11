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
											
											<?php if (session()->has('error')) : ?>
											
											<div class="alert alert-danger">
												<h2><?php echo session('error') ?></h2>
											</div>
											<?php endif ?>
											
											<div class="row">
												<div class="col-sm-12">
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Kotak Penyimpanan</label></div>
														<div class="col-sm-9">
															<select class="form-control select2" name="id_kotak_penyimpanan" id="id_kotak_penyimpanan">
															<option value="">-- pilih kotak penyimpanan</option>
															<?php
															$q = "
																SELECT a.id, a.nama, code_prefix, code_suffix, code_length
																FROM data_kotak_penyimpanan a
																LEFT JOIN data_lab b ON(a.id_lab = b.id)
																WHERE a.id_lab = ".$lab->id."
																ORDER BY nama ASC
															";
															
															$labs = $db->query($q)->getResult();
															
															foreach ($labs as $row)
															{
																echo '
																	<option value="' . $row->id .'">
																		'.$row->nama . ' (' . $row->code_prefix . str_repeat('0', $row->code_length) . $row->code_suffix . ')
																	</option>
																';
															}
															?>
															</select>
														</div>
													</div>
												
													<div class="form-group row" id="form_jml_kotak_penyimpanan">
														<div class="col-sm-3"><label class="font-weight-bold">Jumlah Kotak Penyimpanan</label></div>
														<div class="col-sm-9">
															<input type="number" class="form-control" name="jml_kotak_penyimpanan" id="jml_kotak_penyimpanan"
															placeholder="Jumlah kotak penyimpanan yang akan ditambahkan" disabled>
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
			$(document).ready(function() {
				$('.select2').select2({
					theme: "bootstrap"
				});
			});
			
			required = ["id_kotak_penyimpanan", "jml_kotak_penyimpanan"];
			errornotice = $("#error");
			emptyerror = "";
			
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
			
			$("#id_kotak_penyimpanan").change(function(){
				 $.ajax({
					type: "POST",
					url: '/data/daftarKotakPenyimpananGetInventaris',
					"data": {
						'id_kotak_penyimpanan' : function(){return $("#id_kotak_penyimpanan").val();}
					},
					beforeSend: function(){},
					dataType: "html",
					success: function(data){
						 $("#form_jml_kotak_penyimpanan").html(data);
					}
				});
			});
        </script>
    </body>
</html>