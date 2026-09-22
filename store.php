<?php 

 include 'include/header.php';
 $product = new auth();
 $result = $product->products_fetch();
?>

		<!-- BREADCRUMB -->
		<div id="breadcrumb" class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
					<div class="col-md-12">
						<ul class="breadcrumb-tree">
							<li><a href="index.php">Home</a></li>
							<li class="active">
								<?php 
								if (isset($_GET['cat_id'])) {
									$cat_id = $_GET['cat_id'];
									$cat_name = $product->get_category_name($cat_id);
									echo htmlspecialchars($cat_name ? $cat_name : 'Category');
								} elseif (isset($_GET['search'])) {
									echo 'Search Results';
								} else {
									echo 'All Products';
								}
								?>
							</li>
						</ul>
					</div>
				</div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /BREADCRUMB -->

		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
	          
			
				<!-- ASIDE -->
					<div id="aside" class="col-md-3">
						<!-- aside Widget -->
						<div class="aside">
							<h3 class="aside-title">Categories</h3>
							<div class="checkbox-filter">
                           <?php
						     $categories = $product->select_cat();
							 foreach($categories as $category)
							 {
						   ?>
								<div class="input-checkbox">
									<label for="category-1">
										<span></span>
										<a href="store.php?cat_id=<?php echo $category['id']?>"><?php echo $category['cat_name']?></a>
										
									</label>
								</div>
                            <?php }?>
								
							
							</div>
						</div>
						<!-- /aside Widget -->

						<!-- aside Widget -->
						<div class="aside">
							<h3 class="aside-title">Price</h3>
							<div class="price-filter">
								<div id="price-slider"></div>
								<div class="input-number price-min">
									<input id="price-min" type="number">
									<span class="qty-up">+</span>
									<span class="qty-down">-</span>
								</div>
								<span>-</span>
								<div class="input-number price-max">
									<input id="price-max" type="number">
									<span class="qty-up">+</span>
									<span class="qty-down">-</span>
								</div>
							</div>
						</div>
						<!-- /aside Widget -->
					</div>
					<!-- /ASIDE -->

					<!-- STORE -->
					<div id="store" class="col-md-9">
						<!-- section title -->
						<div class="col-md-12">
							<div class="section-title">
								<h3 class="title">
									<?php 
									if (isset($_GET['cat_id'])) {
										$cat_id = $_GET['cat_id'];
										$cat_name = $product->get_category_name($cat_id);
										echo htmlspecialchars($cat_name ? $cat_name : 'Category Products');
									} elseif (isset($_GET['search'])) {
										echo 'Search Results for: ' . htmlspecialchars($_GET['search']);
									} else {
										echo 'All Products';
									}
									?>
								</h3>
							</div>
						</div>
						<!-- /section title -->
								
						<!-- /store top filter -->
						<!-- store products -->
						<div class="row">
							<!-- product -->
							<?php
							// Fix: Remove stray ending '}' and 'elseif'/else bugs, 
							// unify safe discount calculation, HTML, and PHP tag placement
							if (isset($_GET['cat_id'])) {
								$cat_id = $_GET['cat_id'];
								$cat = $product->fetchByCategory($cat_id);
								$product_list = is_array($cat) ? $cat : [];
							} elseif (isset($_GET['search'])) {
								$search = $_GET['search'];
								$s = $product->search_form($search);
								$product_list = is_array($s) ? $s : [];
							} else {
								$product_list = isset($result) && is_array($result) ? $result : [];
							}
							
							foreach ($product_list as $row):
								$images = $row['images'];
								$new_images = explode(",", $images);
								$p_price = floatval($row['p_price']);
								$p_discount = floatval($row['p_discount']);
								// Safe discount percent calculation
								$percent = 0;
								if ($p_discount > 0 && $p_discount > $p_price) {
									$percent = (($p_discount - $p_price) * 100) / $p_discount;
								}
							?>
							<div class="col-md-3 col-xs-6">
								<a href="product.php?p_id=<?php echo $row['id']?>">	
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
													<span class="sale"><?php echo ceil($percent); ?>%</span>
												<?php endif; ?>
												<span class="new">NEW</span>
											</div>
										</div>
										<div class="product-body">
											<p class="product-category"></p>
												<?php echo !empty($row['cat_name']) ? htmlspecialchars($row['cat_name']) : 'Category'; ?>
											</p>
											<h3 class="product-name">
												<a href="product.php?p_id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['p_name'] ?? ''); ?></a>
											</h3>
											<h4 class="product-price">
												<?php echo number_format($row['p_price'], 0, ',', '.') . ' ₫'; ?>
												<?php if ($row['p_discount'] > 0 && $row['p_discount'] > $row['p_price']) { ?>
													<del class="product-old-price"><?php echo number_format($row['p_discount'], 0, ',', '.') . ' ₫'; ?></del>
												<?php } ?>
											</h4>
											<?php
												$p_id = $row['id'];
												$result1 = $product->total_reviews($p_id);
												$avg = (float)($result1['avg'] ?? 0);
												$result2 = (int)round($avg);
												for ($i = 1; $i < 6; $i++) { 
													if ($result2 >= $i) {
														echo '<span value="'.$i.'"></span><i style="color:red;" class="fa fa-star checked"></i>';
													} else {
														echo '<i class="fa fa-star checked"></i>';
													}
												}
											?>
											<div class="product-btns">
												<a href="product.php?p_id=<?php echo $row['id']; ?>" class="quick-view"><i class="fa fa-eye"></i><span class="tooltipp"></span></a>
											</div>
										</div>
										<!-- <div class="add-to-cart">
											<button class="add-to-cart-btn"><i class="fa fa-shopping-cart"></i> add to cart</button>
										</div> -->
									</div>
								</a>
							</div>
							<?php endforeach; ?>
							<!-- /product -->
						</div>
						<!-- /store products -->
						
						<?php if (count($product_list) > 12): ?>
						<div class="row">
							<div class="col-md-12 text-center">
								<button id="load-more-store" class="primary-btn" style="margin-top:30px;">Load More</button>
							</div>
						</div>
						<?php endif; ?>

						
					</div>
					<!-- /STORE -->
				</div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->

		<!-- NEWSLETTER -->
		<div id="newsletter" class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
					<div class="col-md-12">
						<div class="newsletter">
							<p>Sign Up for the <strong>NEWSLETTER</strong></p>
							<form>
								<input class="input" type="email" placeholder="Enter Your Email">
								<button class="newsletter-btn"><i class="fa fa-envelope"></i> Subscribe</button>
							</form>
							<ul class="newsletter-follow">
								<li>
									<a href="#"><i class="fa fa-facebook"></i></a>
								</li>
								<li>
									<a href="#"><i class="fa fa-twitter"></i></a>
								</li>
								<li>
									<a href="#"><i class="fa fa-instagram"></i></a>
								</li>
								<li>
									<a href="#"><i class="fa fa-pinterest"></i></a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /NEWSLETTER -->

		<!-- FOOTER -->
	<?php 
	
	 include 'include/footer.php';
	?>
		<!-- /FOOTER -->

		<!-- jQuery Plugins -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/slick.min.js"></script>
		<script src="js/nouislider.min.js"></script>
		<script src="js/jquery.zoom.min.js"></script>
		<script src="js/main.js"></script>
		
		<script>
		// Load More functionality for store page
		document.addEventListener('DOMContentLoaded', function() {
			var loadMoreBtn = document.getElementById('load-more-store');
			if (loadMoreBtn) {
				var products = document.querySelectorAll('#store .col-md-4');
				var productsPerRow = 3;
				var shown = 12; // Show first 12 products initially
				
				// Hide products beyond the first 12
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
