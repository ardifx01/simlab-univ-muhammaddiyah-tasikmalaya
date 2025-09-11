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

								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item"><?php echo $namaLab; ?></li>

								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item"><?php echo $id_peminjam; ?></li>
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
                            <div class="col-sm-10">
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
                            <div class="col-sm-2">
                            	<button type="button" id="submit" class="btn btn-block btn-primary">Submit</button>
                            	<button type="button" id="reset" class="btn btn-block btn-danger">Reset</button>
                            </div>
                            <div class="col-sm-10">
								<div class="card">
									<div class="card-body">
										<input type="hidden" id="id_lab" value="<?php echo $idLab; ?>">
										<input type="hidden" id="id_peminjam" value="<?php echo $id_peminjam; ?>">
										<input type="text" id="kode" class="form-control form-control-lg" placeholder="Masukkan / scan kode" autocomplete="off" 
											onblur="setTimeout(() => this.focus(), 0)" autofocus>
									</div>
								</div>
							</div>
                            <div class="col-sm-2">
								<div class="card">
									<div class="card-body">
										<label for="targetTglKembali">Tanggal pengembalian</label><br><br>
										<input class="form-control form-control-lg" type="date" id="targetTglKembali" autocomplete="off" value="">
									</div>
								</div>
							</div>
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
		
        <script>
        	$('#submit').click(function(){
        		const targetTglKembali = document.getElementById('targetTglKembali').value;
        		if(targetTglKembali == '') {
        			$.notify({
        				title: 'Tanggal pengembalian wajib diisi',
        				message: '',
        				icon: 'fa fa-times',
        			},{
        				type: 'danger',
        				placement: {
        					from: 'top',
        					align: 'center'
        				},
        				delay: 1
        			});
        		} else {
        			var kodeItem = $("input[name='kodeItem[]']").map(function () {
	        			return $(this).val();
	        		}).get();

	        		var kodeKotak = $("input[name='kodeKotak[]']").map(function () {
	        			return $(this).val();
	        		}).get();

	        		$.ajax({
	        			url: '/circulation/lend',
	        			type: "POST",
	        			data: {
	        				'idLab': function(){return $("#id_lab").val();},
	        				'idPeminjam': function(){return $("#id_peminjam").val();},
	        				'targetTglKembali' : targetTglKembali,
	        				'kodeItem' : kodeItem,
	        				'kodeKotak' : kodeKotak
	        			},
	        			success: function(data){
	        				location.assign('/circulation/loan');
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
	        			cache: false
	        		});
        		}
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
				});
				
				const tabelKotak = $('#kotak').DataTable({
					ordering: false,
					searching: false,
					paging: false,
					info: false,
				});
				
				const input = document.getElementById('kode');
				
				input.addEventListener('keypress', function(e) {
					if (e.key === 'Enter') {
						const barcode = input.value.trim();
						const existingDataItem = $('input[name="kodeItem[]"]').map(function () {return $(this).val();}).get();
						const existingDataKotak = $('input[name="kodeKotak[]"]').map(function () {return $(this).val();}).get();

						if(existingDataItem.includes(barcode) || existingDataKotak.includes(barcode)) {
							$.notify({
								title: 'Perhatian',
								message: 'Data sudah ada di tabel',
								icon: 'fa fa-info',
							},{
								type: 'warning',
								placement: {
									from: 'top',
									align: 'center'
								},
								delay: 1
							});
						} else {
							if (barcode !== "") {
								$.ajax({
									url: '/circulation/getItem',
									type: "POST",
									data: {
										'kode' : barcode
									},
									success: function(data){
										const info = JSON.parse(data);

										if(info['jenis'] == 'item') {
											tabelItem.row.add([
												'',
												info['nama'],
												'<input style="border:0px;padding:4px;" type="text" name="kodeItem[]" value="' + barcode + '" readonly>',
												''
											]).draw(false);
										} else if(info['jenis'] == 'kotak') {
											tabelKotak.row.add([
												'',
												info['nama'],
												'<input style="border:0px;padding:4px;" type="text" name="kodeKotak[]" value="' + barcode + '" readonly>'
											]).draw(false);
											info['item'].forEach(function (item, index) {
												tabelItem.row.add([
													'',
													item.nama,
													'<input style="border:0px;padding:4px;" type="text" name="kodeItem[]" value="' + item.kode + '" readonly>',
													barcode
												]).draw(false);
											});
										} else {
											$.notify({
												title: 'Terjadi kesalahan',
												message: info['nama'],
												icon: 'fa fa-times',
											},{
												type: 'danger',
												placement: {
													from: 'top',
													align: 'center'
												},
												delay: 1
											});
										}
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
									cache: false
								});
							}
						}
						input.value = ""; // reset input
					}
				});
				
				tabelItem.on('order.dt search.dt draw.dt', function () {
					tabelItem.column(0, { search: 'applied', order: 'applied' })
					.nodes()
					.each(function (cell, i) {
						cell.innerHTML = i + 1;
					});
				});
				
				tabelKotak.on('order.dt search.dt draw.dt', function () {
					tabelKotak.column(0, { search: 'applied', order: 'applied' })
					.nodes()
					.each(function (cell, i) {
						cell.innerHTML = i + 1;
					});
				});
			});
        </script>
    </body>
</html>