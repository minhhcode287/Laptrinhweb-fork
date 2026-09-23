<?php 
 include 'include/header.php';
 $product = new auth();

 // Gather all GET filter parameters
 $filters = [
     'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
     'cat_id' => (!empty($_GET['cat_id']) && is_numeric($_GET['cat_id'])) ? (int)$_GET['cat_id'] : null,
     'brand' => isset($_GET['brand']) ? trim($_GET['brand']) : '',
     'min_price' => (isset($_GET['min_price']) && is_numeric($_GET['min_price']) && $_GET['min_price'] !== '') ? (int)$_GET['min_price'] : null,
     'max_price' => (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] !== '') ? (int)$_GET['max_price'] : null,
     'stock' => isset($_GET['stock']) ? trim($_GET['stock']) : '',
     'rating' => (!empty($_GET['rating']) && is_numeric($_GET['rating'])) ? (float)$_GET['rating'] : null,
     'on_sale' => !empty($_GET['on_sale']) ? 1 : 0,
     'sort' => isset($_GET['sort']) ? trim($_GET['sort']) : 'default',
 ];

 // Execute search with filters
 $product_list = $product->search_products($filters);
 $categories = $product->get_categories_with_count();
 $available_brands = $product->get_available_brands();

 // Determine page heading
 $heading = 'Tất cả sản phẩm';
 if (!empty($filters['search'])) {
     $heading = 'Kết quả tìm kiếm cho: "' . htmlspecialchars($filters['search']) . '"';
 } elseif (!empty($filters['cat_id'])) {
     $cat_name = $product->get_category_name($filters['cat_id']);
     $heading = htmlspecialchars($cat_name ? $cat_name : 'Danh mục sản phẩm');
 } elseif (!empty($filters['brand'])) {
     $heading = 'Sản phẩm thương hiệu: ' . htmlspecialchars($filters['brand']);
 }
?>

		<!-- BREADCRUMB -->
		<div id="breadcrumb" class="section">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<ul class="breadcrumb-tree">
							<li><a href="index.php">Home</a></li>
							<li><a href="store.php">Cửa hàng</a></li>
							<li class="active"><?php echo $heading; ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- /BREADCRUMB -->

		<!-- SECTION -->
		<div class="section">
			<div class="container">
				<div class="row">

				<!-- ASIDE (BỘ LỌC) -->
				<div id="aside" class="col-md-3">
					<form method="GET" action="store.php" id="filter-form">
						
						<!-- Widget: Tìm kiếm -->
						<div class="aside">
							<h3 class="aside-title">Tìm kiếm</h3>
							<div class="form-group" style="position:relative;">
								<input type="text" name="search" class="input" placeholder="Tên sản phẩm, mã, cấu hình..." value="<?php echo htmlspecialchars($filters['search']); ?>" style="padding-right: 40px;">
								<button type="submit" style="position:absolute; right:5px; top:5px; background:none; border:none; color:#D10024; padding:5px 10px; cursor:pointer;"><i class="fa fa-search"></i></button>
							</div>
						</div>

						<!-- Widget: Sắp xếp -->
						<div class="aside">
							<h3 class="aside-title">Sắp xếp theo</h3>
							<select name="sort" class="input" onchange="this.form.submit()" style="cursor:pointer;">
								<option value="default" <?php if($filters['sort'] == 'default') echo 'selected'; ?>>Mặc định (Mới cập nhật)</option>
								<option value="price_asc" <?php if($filters['sort'] == 'price_asc') echo 'selected'; ?>>Giá: Thấp đến Cao</option>
								<option value="price_desc" <?php if($filters['sort'] == 'price_desc') echo 'selected'; ?>>Giá: Cao đến Thấp</option>
								<option value="newest" <?php if($filters['sort'] == 'newest') echo 'selected'; ?>>Mới nhất</option>
								<option value="top_selling" <?php if($filters['sort'] == 'top_selling') echo 'selected'; ?>>Bán chạy nhất</option>
								<option value="rating_desc" <?php if($filters['sort'] == 'rating_desc') echo 'selected'; ?>>Đánh giá cao nhất</option>
								<option value="discount_desc" <?php if($filters['sort'] == 'discount_desc') echo 'selected'; ?>>Giảm giá nhiều nhất</option>
							</select>
						</div>

						<!-- Widget: Danh mục -->
						<div class="aside">
							<h3 class="aside-title">Danh mục</h3>
							<div class="checkbox-filter" style="max-height: 220px; overflow-y: auto;">
								<div class="input-radio" style="margin-bottom: 8px;">
									<input type="radio" name="cat_id" id="cat-all" value="" <?php if(empty($filters['cat_id'])) echo 'checked'; ?> onchange="this.form.submit()">
									<label for="cat-all">
										<span></span>
										Tất cả danh mục
									</label>
								</div>
								<?php foreach($categories as $cat): ?>
								<div class="input-radio" style="margin-bottom: 8px;">
									<input type="radio" name="cat_id" id="cat-<?php echo $cat['id']; ?>" value="<?php echo $cat['id']; ?>" <?php if($filters['cat_id'] == $cat['id']) echo 'checked'; ?> onchange="this.form.submit()">
									<label for="cat-<?php echo $cat['id']; ?>">
										<span></span>
										<?php echo htmlspecialchars($cat['cat_name']); ?>
										<small>(<?php echo (int)$cat['product_count']; ?>)</small>
									</label>
								</div>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Widget: Thương hiệu -->
						<div class="aside">
							<h3 class="aside-title">Thương hiệu</h3>
							<select name="brand" class="input" onchange="this.form.submit()" style="cursor:pointer;">
								<option value="">-- Tất cả thương hiệu --</option>
								<?php foreach($available_brands as $b): ?>
									<option value="<?php echo htmlspecialchars($b); ?>" <?php if(strcasecmp($filters['brand'], $b) === 0) echo 'selected'; ?>>
										<?php echo htmlspecialchars($b); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- Widget: Khoảng giá -->
						<div class="aside">
							<h3 class="aside-title">Khoảng giá (VNĐ)</h3>
							<div class="price-filter" style="margin-bottom: 12px;">
								<div class="row" style="margin: 0 -5px;">
									<div class="col-xs-6" style="padding: 0 5px;">
										<input type="number" name="min_price" class="input" placeholder="Từ (₫)" value="<?php echo $filters['min_price'] !== null ? htmlspecialchars($filters['min_price']) : ''; ?>" min="0" step="500000" style="font-size:13px;">
									</div>
									<div class="col-xs-6" style="padding: 0 5px;">
										<input type="number" name="max_price" class="input" placeholder="Đến (₫)" value="<?php echo $filters['max_price'] !== null ? htmlspecialchars($filters['max_price']) : ''; ?>" min="0" step="500000" style="font-size:13px;">
									</div>
								</div>
							</div>
							<!-- Quick Price Presets -->
							<div style="font-size: 11px; margin-bottom: 15px;">
								<span style="color:#888; display:block; margin-bottom:5px;">Mức giá phổ biến:</span>
								<a href="javascript:void(0)" onclick="setPrice(0, 15000000)" class="btn btn-default btn-xs" style="margin:2px;">&lt; 15tr</a>
								<a href="javascript:void(0)" onclick="setPrice(15000000, 25000000)" class="btn btn-default btn-xs" style="margin:2px;">15 - 25tr</a>
								<a href="javascript:void(0)" onclick="setPrice(25000000, 35000000)" class="btn btn-default btn-xs" style="margin:2px;">25 - 35tr</a>
								<a href="javascript:void(0)" onclick="setPrice(35000000, '')" class="btn btn-default btn-xs" style="margin:2px;">&gt; 35tr</a>
							</div>
						</div>

						<!-- Widget: Tình trạng hàng -->
						<div class="aside">
							<h3 class="aside-title">Tình trạng kho</h3>
							<div class="checkbox-filter">
								<div class="input-radio" style="margin-bottom: 6px;">
									<input type="radio" name="stock" id="stock-all" value="" <?php if(empty($filters['stock'])) echo 'checked'; ?> onchange="this.form.submit()">
									<label for="stock-all"><span></span> Tất cả</label>
								</div>
								<div class="input-radio" style="margin-bottom: 6px;">
									<input type="radio" name="stock" id="stock-in" value="in_stock" <?php if($filters['stock'] == 'in_stock') echo 'checked'; ?> onchange="this.form.submit()">
									<label for="stock-in"><span></span> Còn hàng</label>
								</div>
								<div class="input-radio" style="margin-bottom: 6px;">
									<input type="radio" name="stock" id="stock-low" value="low_stock" <?php if($filters['stock'] == 'low_stock') echo 'checked'; ?> onchange="this.form.submit()">
									<label for="stock-low"><span></span> Sắp hết hàng (&le; 10)</label>
								</div>
								<div class="input-radio" style="margin-bottom: 6px;">
									<input type="radio" name="stock" id="stock-out" value="out_of_stock" <?php if($filters['stock'] == 'out_of_stock') echo 'checked'; ?> onchange="this.form.submit()">
									<label for="stock-out"><span></span> Hết hàng</label>
								</div>
							</div>
						</div>

						<!-- Widget: Đánh giá -->
						<div class="aside">
							<h3 class="aside-title">Đánh giá</h3>
							<select name="rating" class="input" onchange="this.form.submit()" style="cursor:pointer;">
								<option value="">-- Tất cả đánh giá --</option>
								<option value="4" <?php if($filters['rating'] == 4) echo 'selected'; ?>>⭐⭐⭐⭐ Từ 4 sao trở lên</option>
								<option value="3" <?php if($filters['rating'] == 3) echo 'selected'; ?>>⭐⭐⭐ Từ 3 sao trở lên</option>
								<option value="2" <?php if($filters['rating'] == 2) echo 'selected'; ?>>⭐⭐ Từ 2 sao trở lên</option>
							</select>
						</div>

						<!-- Widget: Khuyến mãi -->
						<div class="aside">
							<h3 class="aside-title">Khuyến mãi</h3>
							<div class="checkbox-filter">
								<div class="input-checkbox">
									<input type="checkbox" name="on_sale" id="filter-sale" value="1" <?php if(!empty($filters['on_sale'])) echo 'checked'; ?> onchange="this.form.submit()">
									<label for="filter-sale">
										<span></span>
										<strong style="color:#D10024;"><i class="fa fa-tag"></i> Đang giảm giá</strong>
									</label>
								</div>
							</div>
						</div>

						<!-- Buttons: Lọc & Xóa bộ lọc -->
						<div class="aside" style="border-top:1px solid #E4E7ED; padding-top:15px;">
							<button type="submit" class="primary-btn btn-block" style="border-radius:4px; padding:10px 15px; margin-bottom:10px; width:100%;">
								<i class="fa fa-filter"></i> Áp dụng bộ lọc
							</button>
							<a href="store.php" class="btn btn-default btn-block" style="border-radius:4px; padding:8px 15px; width:100%; text-align:center; display:block;">
								<i class="fa fa-times"></i> Xóa tất cả bộ lọc
							</a>
						</div>

					</form>
				</div>
				<!-- /ASIDE -->

				<!-- STORE (DANH SÁCH SẢN PHẨM) -->
				<div id="store" class="col-md-9">
					<!-- store top filter -->
					<div class="store-filter clearfix" style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #E4E7ED;">
						<div class="pull-left">
							<h3 class="title" style="margin: 0; font-size: 20px;">
								<?php echo $heading; ?>
							</h3>
							<span style="font-size: 13px; color: #777;">
								Tìm thấy <strong style="color: #D10024;"><?php echo count($product_list); ?></strong> sản phẩm
							</span>
						</div>
						<div class="pull-right">
							<?php 
							// Active filter tags
							$hasActive = false;
							?>
							<div style="font-size: 12px; margin-top: 5px;">
								<?php if(!empty($filters['search'])): $hasActive = true; ?>
									<span class="label label-danger" style="padding: 5px 8px; margin-right: 4px; display:inline-block;">
										Từ khóa: <?php echo htmlspecialchars($filters['search']); ?>
									</span>
								<?php endif; ?>
								<?php if(!empty($filters['brand'])): $hasActive = true; ?>
									<span class="label label-primary" style="padding: 5px 8px; margin-right: 4px; display:inline-block;">
										Hãng: <?php echo htmlspecialchars($filters['brand']); ?>
									</span>
								<?php endif; ?>
								<?php if($filters['min_price'] !== null || $filters['max_price'] !== null): $hasActive = true; ?>
									<span class="label label-info" style="padding: 5px 8px; margin-right: 4px; display:inline-block;">
										Giá: <?php echo $filters['min_price'] ? number_format($filters['min_price'], 0, ',', '.') : '0'; ?> ₫ - <?php echo $filters['max_price'] ? number_format($filters['max_price'], 0, ',', '.') : '∞'; ?> ₫
									</span>
								<?php endif; ?>
								<?php if($hasActive): ?>
									<a href="store.php" style="color: #888; text-decoration: underline; margin-left: 5px;">[Xóa lọc]</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<!-- /store top filter -->

					<!-- store products -->
					<div class="row">
						<?php if (empty($product_list)): ?>
							<div class="col-md-12 text-center" style="padding: 50px 20px;">
								<div style="font-size: 48px; color: #ddd; margin-bottom: 15px;">
									<i class="fa fa-search"></i>
								</div>
								<h4 style="color: #555; margin-bottom: 10px;">Không tìm thấy sản phẩm phù hợp.</h4>
								<p style="color: #888; max-width: 450px; margin: 0 auto 20px auto;">
									Hãy thử tìm kiếm với từ khóa khác, nới rộng khoảng giá hoặc xóa bớt các tiêu chí lọc.
								</p>
								<a href="store.php" class="primary-btn" style="border-radius:4px; padding: 10px 20px;">
									<i class="fa fa-th"></i> Xem tất cả sản phẩm
								</a>
							</div>
						<?php else: ?>
							<?php foreach ($product_list as $row): 
								$images = $row['images'];
								$new_images = explode(",", $images);
								$p_price = floatval($row['p_price']);
								$p_discount = floatval($row['p_discount']);
								$percent = 0;
								if ($p_discount > 0 && $p_discount > $p_price) {
									$percent = (($p_discount - $p_price) * 100) / $p_discount;
								}
								// Safe image candidate
								$_st_img = 'default.png';
								foreach ($new_images as $_candidate) {
									$_candidate = basename(trim(str_replace('../../uploads/', '', $_candidate)));
									if ($_candidate !== '' && file_exists(__DIR__ . '/uploads/' . $_candidate)) {
										$_st_img = $_candidate;
										break;
									}
								}
								$avg_rating = isset($row['avg_rating']) ? (float)$row['avg_rating'] : 0;
								$rounded_stars = (int)round($avg_rating);
								$stock_qty = isset($row['quantity']) ? (int)$row['quantity'] : 0;
							?>
							<div class="col-md-4 col-xs-6 store-product-item">
								<a href="product.php?p_id=<?php echo $row['id']; ?>">
									<div class="product">
										<div class="product-img">
											<?php
									$_st_img = 'default.png';
									foreach ($new_images as $_candidate) {
										$_candidate = basename(trim(str_replace('../../uploads/', '', $_candidate)));
										if ($_candidate !== '' && file_exists(__DIR__ . '/uploads/' . $_candidate)) {
											$_st_img = $_candidate;
											break;
										}
									}
								?>
								<img width="100px" height="280px" src="./uploads/<?php echo htmlspecialchars($_st_img); ?>" alt="<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>">
											<div class="product-label">
												<?php if ($percent > 0): ?>
													<span class="sale">-<?php echo ceil($percent); ?>%</span>
												<?php endif; ?>
												<?php if ($stock_qty <= 0): ?>
													<span class="sale" style="background:#777;">Hết hàng</span>
												<?php elseif ($stock_qty <= 5): ?>
													<span class="new" style="background:#E67E22;">Còn <?php echo $stock_qty; ?> máy</span>
												<?php endif; ?>
											</div>
										</div>
										<div class="product-body">
											<p class="product-category">
												<?php echo !empty($row['cat_name']) ? htmlspecialchars($row['cat_name']) : 'Category'; ?>
												<?php if(!empty($row['sub_cat_name'])): ?>
													&bull; <?php echo htmlspecialchars($row['sub_cat_name']); ?>
												<?php endif; ?>
											</p>
											<h3 class="product-name" style="height: 40px; overflow: hidden;">
												<a href="product.php?p_id=<?php echo $row['id']; ?>" title="<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>">
													<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>
												</a>
											</h3>
											<h4 class="product-price">
												<?php echo number_format($row['p_price'], 0, ',', '.') . ' ₫'; ?>
												<?php if ($row['p_discount'] > 0 && $row['p_discount'] > $row['p_price']) { ?>
													<del class="product-old-price"><?php echo number_format($row['p_discount'], 0, ',', '.') . ' ₫'; ?></del>
												<?php } ?>
											</h4>
											<div class="product-rating" style="margin-bottom: 5px;">
												<?php
													for ($i = 1; $i <= 5; $i++) {
														if ($rounded_stars >= $i) {
															echo '<i class="fa fa-star" style="color:#D10024;"></i> ';
														} else {
															echo '<i class="fa fa-star-o" style="color:#ccc;"></i> ';
														}
													}
												?>
												<?php if(!empty($row['review_count']) && $row['review_count'] > 0): ?>
													<small style="color:#888;">(<?php echo (int)$row['review_count']; ?>)</small>
												<?php endif; ?>
											</div>
											<div class="product-btns">
												<a href="product.php?p_id=<?php echo $row['id']; ?>" class="quick-view"><i class="fa fa-eye"></i><span class="tooltipp">Chi tiết</span></a>
											</div>
										</div>
									</div>
								</a>
							</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<!-- /store products -->

					<?php if (count($product_list) > 12): ?>
					<div class="row">
						<div class="col-md-12 text-center">
							<button id="load-more-store" class="primary-btn" style="margin-top:30px; border-radius:4px;">
								<i class="fa fa-chevron-down"></i> Xem thêm sản phẩm
							</button>
						</div>
					</div>
					<?php endif; ?>

				</div>
				<!-- /STORE -->

				</div>
			</div>
		</div>
		<!-- /SECTION -->

		<!-- NEWSLETTER -->
		<div id="newsletter" class="section">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="newsletter">
							<p>Đăng ký nhận <strong>BẢN TIN KHUYẾN MÃI</strong></p>
							<form>
								<input class="input" type="email" placeholder="Nhập email của bạn">
								<button class="newsletter-btn"><i class="fa fa-envelope"></i> Đăng ký</button>
							</form>
							<ul class="newsletter-follow">
								<li><a href="#"><i class="fa fa-facebook"></i></a></li>
								<li><a href="#"><i class="fa fa-twitter"></i></a></li>
								<li><a href="#"><i class="fa fa-instagram"></i></a></li>
								<li><a href="#"><i class="fa fa-pinterest"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /NEWSLETTER -->

		<!-- FOOTER -->
		<?php include 'include/footer.php'; ?>
		<!-- /FOOTER -->

		<!-- jQuery Plugins -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/slick.min.js"></script>
		<script src="js/nouislider.min.js"></script>
		<script src="js/jquery.zoom.min.js"></script>
		<script src="js/main.js"></script>
		
		<script>
		// Helper for price preset buttons
		function setPrice(min, max) {
			var f = document.getElementById('filter-form');
			if (!f) return;
			f.elements['min_price'].value = min;
			f.elements['max_price'].value = max;
			f.submit();
		}

		// Load More functionality
		document.addEventListener('DOMContentLoaded', function() {
			var loadMoreBtn = document.getElementById('load-more-store');
			if (loadMoreBtn) {
				var products = document.querySelectorAll('.store-product-item');
				var productsPerRow = 3;
				var shown = 12;
				
				for (var i = shown; i < products.length; i++) {
					products[i].style.display = 'none';
				}
				
				loadMoreBtn.addEventListener('click', function() {
					for (var i = shown; i < shown + productsPerRow && i < products.length; i++) {
						products[i].style.display = '';
					}
					shown += productsPerRow;
					if (shown >= products.length) {
						loadMoreBtn.style.display = 'none';
					}
				});
			}
		});
		</script>
	</body>
</html>
