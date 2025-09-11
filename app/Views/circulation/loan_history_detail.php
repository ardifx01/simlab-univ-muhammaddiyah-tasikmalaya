<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
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
                            <div class="col-sm-12">
								<div class="card">
									<div class="card-body">
										<div class="table-responsive">
											<table>
												<tr>
													<td>
														<div class="h2 font-weight-bold">Laboratorium</div>
													</td>
													<td>
														<div class="h2 font-weight-bold">:</div>
													</td>
													<td>
														<div class="h2"><?php echo $namaLab; ?></div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="h2 font-weight-bold mr-4">Nama Peminjam</div>
													</td>
													<td>
														<div class="h2 font-weight-bold mr-2">:</div>
													</td>
													<td>
														<div class="h2"><?php echo $nama; ?></div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="h2 font-weight-bold">No. Induk</div>
													</td>
													<td>
														<div class="h2 font-weight-bold">:</div>
													</td>
													<td>
														<div class="h2"><?php echo $id_peminjam; ?></div>
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>

                            <input type="hidden" id="id_lab" value="<?php echo $idLab; ?>">
							<input type="hidden" id="id_peminjam" value="<?php echo $id_peminjam; ?>">
							
                            <div class="col-sm-6">
								<div class="card">
									<div class="card-header">
										<h2>Item</h2>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-striped table-hover" id="item">
												<thead>
													<tr>
														<th>No.</th>
														<th>Nama Item</th>
														<th>Kode Item</th>
														<th>Kode Kotak</th>
														<th>Ada</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>
							</div>
                            <div class="col-sm-6">
								<div class="card">
									<div class="card-header">
										<h2>Kotak</h2>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-striped table-hover" id="kotak">
												<thead>
													<tr>
														<th>No.</th>
														<th>Nama Kotak</th>
														<th>Kode Kotak</th>
														<th>Ada</th>
													</tr>
												</thead>
											</table>
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
		<script src="/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
		<script src="/assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
		<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
		<script src="/assets/js/ready.min.js"></script>
		
		<script src="/assets/js/plugin/datatables/datatables.min.js"></script>

		<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
		
        <script>
        	$('#submit').on('click', function () {
        		swal({
					title: "Selesaikan pengembalian?",
					text: "",
					icon: "warning",
					buttons:{
						cancel: {
							visible: true,
							text : "Batal",
							className: "btn"
						},
						confirm: {
							text : "Ya",
							className : "btn btn-primary"
						}
					}
				})
				.then((done) => {
					if (done) {
						let kodeItem = [];
						$('input[name="kodeItem[]"]:checked').each(function () {
							kodeItem.push($(this).val());
						});

						let kodeKotak = [];
						$('input[name="kodeKotak[]"]:checked').each(function () {
							kodeKotak.push($(this).val());
						});

						// Kirim via AJAX
						$.ajax({
							url: '/circulation/returnAction',
							method: 'POST',
							data: {
								'idPeminjam': function(){return $("#id_peminjam").val();},
								'kodeItem': kodeItem,
								'kodeKotak': kodeKotak
							},
							success: function () {
								window.location.assign('/circulation/return')
							},
							error: function (xhr, ajaxOptions, thrownError) {
								$.notify({
									title: 'Terjadi kesalahan',
									message: 'error ' + xhr.responseText,
									icon: 'fa fa-times',
								},{
									type: 'danger',
									placement: {
										from: 'top',
										align: 'center'
									},
									delay: 1
								});
							},
						});
					}
				});
			});

        	$('#reset').click(function(){
        		location.reload();
        	});

			$(document).ready(function() {
				const tabelItem = $('#item').DataTable({
					ordering: false,
					searching: false,
					paging: false,
					info: false,
					processing: true,
					serverSide: true,
					ajax: {
						url: '<?php echo $url_item ?>',
						type: 'POST',
						data: {
							'id_peminjaman': <?php echo $idPeminjaman; ?>,
							'id_peminjam': function(){return $("#id_peminjam").val();},
							'selesai': 1
						}
					}
				});
				
				const tabelKotak = $('#kotak').DataTable({
					ordering: false,
					searching: false,
					paging: false,
					info: false,
					processing: true,
					serverSide: true,
					ajax: {
						url: '<?php echo $url_kotak ?>',
						type: 'POST',
						data: {
							'id_peminjaman': <?php echo $idPeminjaman; ?>,
							'id_peminjam': function(){return $("#id_peminjam").val();},
							'selesai': 1
						}
					}
				});
			});
        </script>
    </body>
</html>