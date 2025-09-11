<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link name="/assets/css/datatables/responsive.bootstrap.min.css" rel="stylesheet">
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
								<form action="" method="post">
									<input type="hidden" name="id" value="<?= $id ?>">
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
															<select class="form-control select2" name="id_lab" id="id_lab" onchange="getRooms()">
																<?php foreach ($labs as $row): ?>
																	<option value="<?=$row->id?>"><?=$row->nama?></option>
																<?php endforeach; ?>
															</select>
														</div>
													</div>
												
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Ruangan</label></div>
														<div class="col-sm-9">
															<select class="form-control select2" id="id_room" name="id_room">
																<option value="">-- Pilih ruangan</option>
															</select>
														</div>
													</div>

													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Penanggun jawab</label></div>
														<div class="col-sm-9">
															<select class="form-control select2" id="id_dosen" name="id_dosen">
																<option value="">-- Pilih penanggun jawab</option>
																<?php foreach ($dosen as $row): ?>
																	<option value="<?=$row->id?>"><?=$row->nama_sdm?></option>
																<?php endforeach; ?>
															</select>
														</div>
													</div>

													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Nama Kegiatan</label></div>
														<div class="col-sm-9">
															<input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Kegiatan">
														</div>
													</div>
													
													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Deskripsi</label></div>
														<div class="col-sm-9">
															<textarea rows="3" class="form-control" name="deskripsi" id="deskripsi" placeholder="Deskripsi"></textarea>
														</div>
													</div>

													<div class="form-group row">
														<div class="col-sm-3"><label class="font-weight-bold">Pelaksanaan</label></div>
														<div class="col-sm-3">
															<label for="tgl">Tanggal</label>
															<input type="date" class="form-control" name="tgl" id="tgl">
														</div>
														<div class="col-sm-3">
															<label>Waktu Mulai</label>
															<div class="row">
																<div class="col-lg-6 col-xs-6">
																	<select class="form-control" id="h_mulai" name="h_mulai">
																		<?php for($h_mulai=0; $h_mulai<=23; $h_mulai++): ?>
																			<option value="<?=sprintf("%02d", $h_mulai)?>"><?=sprintf("%02d", $h_mulai)?></option>
																		<?php endfor; ?>
																	</select>
																</div>
																<div class="col-lg-6 col-xs-6">
																	<select class="form-control" id="i_mulai" name="i_mulai">
																		<?php for($i=0; $i<=59; $i++): ?>
																			<option value="<?=sprintf("%02d", $i)?>"><?=sprintf("%02d", $i)?></option>
																		<?php endfor; ?>
																	</select>
																</div>
															</div>
														</div>
														<div class="col-sm-3">
															<label>Waktu Selesai</label>
															<div class="row">
																<div class="col-lg-6 col-xs-6">
																	<select class="form-control" id="h_selesai" name="h_selesai">
																		<?php for($h_selesai=0; $h_selesai<=23; $h_selesai++): ?>
																			<option value="<?=sprintf("%02d", $h_selesai)?>"><?=sprintf("%02d", $h_selesai)?></option>
																		<?php endfor; ?>
																	</select>
																</div>
																<div class="col-lg-6 col-xs-6">
																	<select class="form-control" id="i_selesai" name="i_selesai">
																		<?php for($i=0; $i<=59; $i++): ?>
																			<option value="<?=sprintf("%02d", $i)?>"><?=sprintf("%02d", $i)?></option>
																		<?php endfor; ?>
																	</select>
																</div>
															</div>
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
        	$( document ).ready(function() {
        		getRooms();
        		$('.select2').select2({
					theme: "bootstrap"
				});
        	});
			required = ['id_lab', 'id_room', 'id_dosen', 'nama', 'tgl'];
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

			function getRooms() {
				let id_lab = $('#id_lab').val();
				$.ajax({
					url: '/activity/getRooms/' + id_lab,
					type: "GET",

					success: function(data){
						$('#id_room').html(data);
					},
					error: function (xhr, ajaxOptions, thrownError) {
						alert('Error ' + xhr.status);
					},
					cache: false
				});
			}
        </script>
    </body>
</html>