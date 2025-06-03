<?php
// Enable error reporting for diagnostics (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the default timezone to match your other files
date_default_timezone_set('Asia/Kolkata');

// Start or resume session
session_start();

// Check if user is logged in via session
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Check if user is logged in via cookies (if session is not already set)
if (!$loggedIn && isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $loggedIn = true;
    $username = $_SESSION['username'];
}

// Check if user.php exists before trying to load it
$user_php_exists = file_exists('user.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- External CSS -->
    <link rel="stylesheet" href="./css/style.css" />
    <!-- Font Awesome {animation}  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"/>
     <!-- <link rel="stylesheet" href="./css/font-awesome.min.css" /> -->
    <!-- Swiper -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/> -->
     <link rel="stylesheet" href="./css/swiper-bundle.min.css">
    <link rel="icon" type="image/svg"  href="./img/bookfavicon.svg" />
    <title>Udhhan- The flight of Knowledge</title>
    <style>
      img {
        filter:saturate(1.26); filter: contrast(1.21); filter: brightness(1.07);
      }
	.cimg { 
height: 24.5rem; width: 20em; filter: brightness(1.10); padding-left: 1.8rem; padding-right: 1.8rem;
filter:saturate(1.25);
}
.nimg { 
height: 100%; width: 11.20em; filter: saturate(1.30); filter:contrast(1.20); filter: brightness(1.10);
}
.sico {
  width: 28px; 
}

/* Popup Container */
.popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  justify-content: center;
  align-items: center;
}

/* Popup Content */
.popup-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  position: relative;
  max-width: 400px;
  width: 90%;
}

/* Close Button */
.close-popup {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  cursor: pointer;
}

/* Book Cover */
.popup-book-cover {
  max-width: 100%;
  height: auto;
  margin-bottom: 15px;
}

/* gemni css start */
#image-search-btn {
    cursor: pointer;
    font-size: 1.5rem; /* Adjust size as needed */
    margin-left: 0.5rem; /* Spacing from search icon */
    color: #333; /* Icon color */
}

#image-search-btn:hover {
    color: #386c5df0; /* Hover color */
}

/* Responsive adjustments (example) */
@media (max-width: 991px) {
  .cimg { 
 width: 19em; 
 /* padding-left: 0.4rem; padding-right: 0.4rem;  */
 height: 22rem;
}
.nimg { 
height: 100%; width: 12.20em;}
}

@media (max-width: 768px) {
    #image-search-btn {
        font-size: 1.2rem; /* Smaller on smaller screens */
        margin-left: 0.25rem;
    }
    .cimg {
    width: 18em; padding-left: 0.85rem; padding-right: 0.85rem; height: 20rem;
  }
  .nimg { 
height: 100%; width: 11.20em;
}
}



@media (max-width: 450px) {
  .cimg { 
height: 15em; width: 18.5em; padding-left: 1.4rem; padding-right: 1.4rem;
}
.nimg { 
height: 100%; width: 11em;
}
.sico {
    width: 18px; height: 18px;
  }
}
</style>
  </head>
  <body>
    <!-- Header Start  -->
    <header class="header">
      <!-- Header 1 Start  -->
      <div class="header-1">
        <a href="./index.php" class="logo"><i class="fas fa-book"></i> Udhhan- The flight of education</a>

         <form action="" class="search-form">
            <input type="search" name="query" placeholder="<?php echo $loggedIn ? 'Looking for a book ' . htmlspecialchars($username) . '?' : 'Search...'; ?>" id="search-box" aria-label="Search books" required/>
            <button type="submit" style="margin-right: 0.4rem;" class="fas fa-search"></button>
        </form>

      <!-- Icons Section -->
      <div class="icons">
        <!-- Standard Search Button -->
        <div id="search-btn" class="fas fa-search"></div>
    
        <!-- Image Search Button -->
        <div id="image-search-btn" class="fas"><img src="./img/camera.svg" class="sico fas" alt="image-search"></div>
        <input type="file" id="image-upload" accept="image/*" style="display: none;">
    
        <!-- Favorite Icon -->
        <a href="./fav.html" class="fas fa-heart-circle-check" aria-label="Favorites"></a>
    
        <!-- Shopping Cart -->
        <a href="./cart.php" class="fas fa-shopping-cart" aria-label="Shopping Cart"></a>
    
        <!-- Login Icon -->
        <a id="login-btn" class="fa-solid fa-user" href="./login.php" aria-label="Login"></a>
      </div>
    </div>
      </div>
      <!-- Header 1 End -->

      <!-- Header 2 Start -->
      <div class="header-2">
        <div class="navbar">
          <a class="active" href="#home">Home</a>
          <a href="#about">About</a>
          <a href="#populer">Popular</a>
          <a href="#new">New</a>
          <a href="#reviews">Reviews</a>
          <a href="#member">Member</a>
          <a href="#blogs">Blogs</a>
        </div>
      </div>
      <!-- Header 2 End -->
    </header>
    <!-- Header End -->

    <!-- Bottom Navbar Start -->
    <div class="bottom-navbar">
      <a href="#home" class="fas fa-home"></a>
      <a href="#about" class="fas fa-people-group"></a>
      <a href="#populer" class="fas fa-fire"></a>
      <a href="#new" class="fas fa-book-bookmark"></a>
      <a href="#reviews" class="fas fa-star"></a>
      <a href="#member" class="fas fa-user-plus"></a>
      <!-- <a href="#blogs" class="fas fa-newspaper"></a> -->
    </div>
    <!-- Bottom Navbar End -->

    <!-- Home Section Start -->
    <section class="home" id="home">
      <div class="row">
        <div class="content">
          <h3>Books Feed Your Soul</h3>
          <p>
            He created a special page, A Year of Books, where you can find new books and participate in discussions about them. The picked books help to learn about different cultures, beliefs, histories and technologies.
          </p>
          <a href="#populer" class="btn">Shop Now !</a>
        </div>

        <div class="swiper books-slider">
          <div class="swiper-wrapper">
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/9e62a067.png" alt=""
            /></a>
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/a6602be0.png" alt=""
            /></a>
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/d960d7b6.png" alt=""
            /></a>
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/a0126546.png" alt=""
            /></a>
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/3f7f8774.png" alt=""
            /></a>
            <a href="#" class="swiper-slide"
              ><img class="cimg" src="./book_covers/7fb21f4e.png" alt=""
            /></a>
          </div>
          <img src="./img/stand.png" class="stand" alt="" />
        </div>
      </div>
    </section>
    <!-- Home Section End -->

    <!-- About Us Start -->
    <section id="about" class="about">
      <div class="container">
        <h1>WHY CHOOSE US?</h1>
        <div class="row">
          <div class="image">
            <img src="./img/img4.svg" alt="" />
          </div>

          <div class="content">
            <h3>best book store in the world</h3>
            <p>
              With an exclusive Virtual online launch party, using all aspect of technology and special algorithms…  our goal is to sell books and push your book to bestseller status.
            </p>
            <p>
              It’s a  strategy for getting maximum exposure for your book, and building an online marketing campaign that will continue selling your book long after its initial launch.
            </p>
            <div class="icons-container">
              <div class="icons">
                <i class="fas fa-shield"></i>
                <span>Safe delivery</span>
              </div>
              <div class="icons">
                <i class="fas fa-wallet"></i>
                <span>easy payments</span>
              </div>
              <div class="icons">
                <i class="fas fa-headset"></i>
                <span>24/7 service</span>
              </div>
            </div>
            <a href="./404Error.html" class="btn">learn more</a>
          </div>
        </div>
      </div>
    </section>
    <!-- About Us End -->
    <!-- Popular starts  -->

    <!-- <section class="populer" id="populer">
      <h1 class="heading"><span> Popular Books</span></h1>
      <div class="swiper populer-slider">
          <div class="swiper-wrapper">
  
              <div class="swiper-slide box" data-tags="rich dad robert kiyosaki finance">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/8ceacc60.png" alt="Rich Dad Poor Dad" />
                  </div>
                  <div class="content">
                      <h3>Rich Dad Poor Dad</h3>
                      <div class="price">Free <span>₹. 299.000</span></div>
                      <a href="/content/Finance/Rich Dad Poor Dad.pdf" class="btn" download="Rich Dad Poor Dad.pdf">Downlaod Sample</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="artificial intelligence ai computer science">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/80f921dc.png" alt="Artificial Intelligence" />
                  </div>

                  <div class="content">
                      <h3>Artificial Intelligence</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="machine learning ml computer science machine learning">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/9316cd4d.png" alt="Machine Learning" />
                  </div>
                  <div class="content">
                      <h3>Machine Learning</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="cybersecurity cybersec computer science cybersecurity">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/6fe2ab15.png" alt="Cybersecurity Essentials" />
                  </div>
                  <div class="content">
                      <h3>Cybersecurity Essentials</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="atomic habits james clear self motivation habits">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/9112a9bf.png" alt="Atomic Habits" />
                  </div>
                  <div class="content">
                      <h3>Atomic Habits</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="think grow rich motivation finance rich think grow">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/2bbefac9.png" alt="Think-And-Grow-Rich" />
                  </div>
                  <div class="content">
                      <h3>Think And Grow Rich</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="fiction 1984">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/4dea5c7c.png" alt="1984" />
                  </div>
                  <div class="content">
                      <h3>1984</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="fiction pride prejudice romance">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/8794595d.png" alt="pride-and-prejudice" />
                  </div>
                  <div class="content">
                      <h3>Pride and Prejudice</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
  
              <div class="swiper-slide box" data-tags="fiction harry potter potter harry cursed child child">
                  <div class="icons">
                      <a href="#populer" class="fas fa-search"></a>
                      <a href="#populer" class="fas fa-heart-circle-plus"></a>
                      <a href="#populer" class="fas fa-info"></a>
                  </div>
                  <div class="image">
                      <img src="./book_covers/148e4c32.png" alt="harry_potter_and_the_cursed_child" />
                  </div>
                  <div class="content">
                      <h3>Harry Potter and the Cursed Child</h3>
                      <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                      <a href="#populer" class="btn">Add To Cart</a>
                  </div>
              </div>
          </div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
      </div>
  </section> -->

  <section class="populer" id="populer">
    <h1 class="heading"><span> Popular Books</span></h1>
    <div class="swiper populer-slider">
        <div class="swiper-wrapper">

            <div class="swiper-slide box" data-book-id="8ceacc60" data-tags="rich dad robert kiyosaki finance">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('8ceacc60')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/8ceacc60.png" alt="Rich Dad Poor Dad" />
                </div>
                <div class="content">
                    <h3>Rich Dad Poor Dad</h3>
                    <div class="author">By Robert Kiyosaki</div>
                    <div class="price">Free <span>₹. 299.000</span></div>
                    <a href="./content/Finance/Rich Dad Poor Dad.pdf" class="btn" download="Rich Dad Poor Dad.pdf">Downlaod Sample</a>
                   
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="80f921dc" data-tags="artificial intelligence ai computer science">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('80f921dc')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/80f921dc.png" alt="Artificial Intelligence" />
                </div>

                <div class="content">
                    <h3>Artificial Intelligence</h3>
                    <div class="author">Various Authors</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('80f921dc')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="9316cd4d" data-tags="machine learning ml computer science machine learning">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('9316cd4d')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/9316cd4d.png" alt="Machine Learning" />
                </div>
                <div class="content">
                    <h3>Machine Learning</h3>
                    <div class="author">Various Authors</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('9316cd4d')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="6fe2ab15" data-tags="cybersecurity cybersec computer science cybersecurity">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('6fe2ab15')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/6fe2ab15.png" alt="Cybersecurity Essentials" />
                </div>
                <div class="content">
                    <h3>Cybersecurity Essentials</h3>
                    <div class="author">Various Authors</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('6fe2ab15')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="9112a9bf" data-tags="atomic habits james clear self motivation habits">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('9112a9bf')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/9112a9bf.png" alt="Atomic Habits" />
                </div>
                <div class="content">
                    <h3>Atomic Habits</h3>
                    <div class="author">By James Clear</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('9112a9bf')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="2bbefac9" data-tags="think grow rich motivation finance rich think grow">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('2bbefac9')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/2bbefac9.png" alt="Think-And-Grow-Rich" />
                </div>
                <div class="content">
                    <h3>Think And Grow Rich</h3>
                    <div class="author">By Napoleon Hill</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('2bbefac9')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="4dea5c7c" data-tags="fiction 1984">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('4dea5c7c')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/4dea5c7c.png" alt="1984" />
                </div>
                <div class="content">
                    <h3>1984</h3>
                    <div class="author">By George Orwell</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('4dea5c7c')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="8794595d" data-tags="fiction pride prejudice romance">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('8794595d')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/8794595d.png" alt="pride-and-prejudice" />
                </div>
                <div class="content">
                    <h3>Pride and Prejudice</h3>
                    <div class="author">By Jane Austen</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('8794595d')">Add To Cart</a>
                </div>
            </div>

            <div class="swiper-slide box" data-book-id="148e4c32" data-tags="fiction harry potter potter harry cursed child child">
                <div class="icons">
                    <a href="#populer" class="fas fa-search"></a>
                    <a href="#populer" class="fas fa-heart-circle-plus" onclick="addToFavorites('148e4c32')"></a>
                    <a href="#populer" class="fas fa-info"></a>
                </div>
                <div class="image">
                    <img src="./book_covers/148e4c32.png" alt="harry_potter_and_the_cursed_child" />
                </div>
                <div class="content">
                    <h3>Harry Potter and the Cursed Child</h3>
                    <div class="author">By J.K. Rowling, John Tiffany, and Jack Thorne</div>
                    <div class="price">₹. 149.00 <span>₹. 199.00</span></div>
                    <a href="#populer" class="btn add-cart-btn" onclick="popcart('148e4c32')">Add To Cart</a>
                </div>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

  <!-- Popular end -->
   <!-- new section start -->
  <section class="new" id="new">
      <h1 class="heading"><span>New Books</span></h1>
      <div class="swiper new-slider">
          <div class="swiper-wrapper">
  
              <a href="#new" class="swiper-slide box" data-tags="computer science networking computer computer networking">
                  <div class="image">
                      <img class="nimg" src="./book_covers/1d1f550f.png" alt="Computer Networking" />
                  </div>
                  <div class="content">
                      <h3>Computer Networking</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
  
              <a href="#new" class="swiper-slide box" data-tags="computer science operating system systems os operating operating_system_concepts">
                  <div class="image">
                      <img class="nimg" src="./book_covers/fa4e56c1.png" alt="Operating_System_Concepts" />
                  </div>
                  <div class="content">
                      <h3>Operating System Concepts</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
  
              <a href="#new" class="swiper-slide box" data-tags="computer science web scraping web python scraping web-scraping-python-2nd">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a0462257.png" alt="web-scraping-python-2nd" />
                  </div>
                  <div class="content">
                      <h3>Web Scraping with Python</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
  
              <a href="#new" class="swiper-slide box" data-tags="computer science algorithms introduction.to.algorithms.4th">
                  <div class="image">
                      <img class="nimg" src="./book_covers/0e652f2a.png" alt="Introduction.to.Algorithms.4th.Leiserson.Stein.Rivest.Cormen.MIT.Press.9780262046305.EBooksWorld.ir" />
                  </div>
                  <div class="content">
                      <h3>Introduction to Algorithms</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
  
              <a href="#new" class="swiper-slide box" data-tags="motivation self improvement 7 habbits habits effective the 7 habits of highly effective peo">
                  <div class="image">
                      <img class="nimg" src="./book_covers/885fc2bf.png" alt="The 7 Habits of Highly Effective Peo" />
                  </div>
                  <div class="content">
                      <h3>The 7 Habits of Highly Effective People</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="motivation self you win you can win">
                  <div class="image">
                      <img class="nimg" src="./book_covers/6932d8ff.png" alt="You Can Win" />
                  </div>
                  <div class="content">
                      <h3>You Can Win</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="motivation canthurt can't hurt">
                  <div class="image">
                      <img class="nimg" src="./book_covers/26c314ff.png" alt="canthurt" />
                  </div>
                  <div class="content">
                      <h3>Can't Hurt Me</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="motivation power power of positive thinking positive thinking">
                  <div class="image">
                      <img class="nimg" src="./book_covers/3f7f8774.png" alt="the-power-of-positive-thinking" />
                  </div>
                  <div class="content">
                      <h3>The Power of Positive Thinking</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="romance states 2-states">
                  <div class="image">
                      <img class="nimg" src="./book_covers/b81061c4.png" alt="2-states" />
                  </div>
                  <div class="content">
                      <h3>2 States</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
            
               <a href="#new" class="swiper-slide box" data-tags="romance pride prejudice">
                  <div class="image">
                      <img class="nimg" src="./book_covers/8794595d.png" alt="pride-and-prejudice" />
                  </div>
                  <div class="content">
                      <h3>Pride and Prejudice</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="romance someone like you some one one">
                  <div class="image">
                      <img class="nimg" src="./book_covers/ddb801f4.png" alt="some one like you" />
                  </div>
                  <div class="content">
                      <h3>Someone Like You</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="romance notebook book note the the notebook">
                  <div class="image">
                      <img class="nimg" src="./book_covers/b55633ad.png" alt="the notebook" />
                  </div>
                  <div class="content">
                      <h3>The Notebook</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="story aesops fabies aesops fables">
                  <div class="image">
                      <img class="nimg" src="./book_covers/8ace65c3.png" alt="Aesops fables" />
                  </div>
                  <div class="content">
                      <h3>Aesop's Fables</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="story arabian nights knight arabic mystery arabian nights">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a214717a.png" alt="Arabian Nights" />
                  </div>
                  <div class="content">
                      <h3>Arabian Nights</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="story tantra panch pancha panchatantra-">
                  <div class="image">
                      <img class="nimg" src="./book_covers/e7ecf12f.png" alt="Panchatantra-" />
                  </div>
                  <div class="content">
                      <h3>Panchatantra</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="story content bond ruskin umbrella blue the blue umbrella by ruskin bond">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a19106c6.png" alt="The Blue Umbrella by Ruskin Bond" />
                  </div>
                  <div class="content">
                      <h3>The Blue Umbrella</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="story storybooks for kids jungle kids jungle books the-jungle-books">
                  <div class="image">
                      <img class="nimg" src="./book_covers/d0f494e5.png" alt="The-Jungle-Books" />
                  </div>
                  <div class="content">
                      <h3>The Jungle Book</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="history constitution legal constituion">
                  <div class="image">
                      <img class="nimg" src="./book_covers/4a9b3adf.png" alt="constituion" />
                  </div>
                  <div class="content">
                      <h3>Constitution of India</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="history hind swaraj hind_swaraj">
                  <div class="image">
                      <img class="nimg" src="./book_covers/ca561c48.png" alt="hind_swaraj" />
                  </div>
                  <div class="content">
                      <h3>Hind Swaraj</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="history indian indian h">
                  <div class="image">
                      <img class="nimg" src="./book_covers/9d8d789a.png" alt="indian h" />
                  </div>
                  <div class="content">
                      <h3>Indian History</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
  
          </div>
      </div>
      <div class="swiper new-slider-2">
          <div class="swiper-wrapper">
              <a href="#new" class="swiper-slide box" data-tags="fiction war peace war and peace war-and-peace">
                  <div class="image">
                      <img class="nimg" src="./book_covers/f96700b4.png" alt="war-and-peace" />
                  </div>
                  <div class="content">
                      <h3>War and Peace</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          
                      </div>
                  </div>
              </a>
              <a href="#new" class="swiper-slide box" data-tags="fiction jane eyrne jane-eyre">
                  <div class="image">
                      <img class="nimg" src="./book_covers/b0f89e31.png" alt="jane-eyre" />
                  </div>
                  <div class="content">
                      <h3>Jane Eyre</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
              <a href="#new" class="swiper-slide box" data-tags="fiction to kill to kill">
                  <div class="image">
                      <img class="nimg" src="./book_covers/3ecff90d.png" alt="to kill" />
                  </div>
                  <div class="content">
                      <h3>To Kill a Mockingbird</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
              <a href="#new" class="swiper-slide box" data-tags="fiction great gatsby the-great-gatsby">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a35857d8.png" alt="the-great-gatsby" />
                  </div>
                  <div class="content">
                      <h3>The Great Gatsby</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
              <a href="#new" class="swiper-slide box" data-tags="history diary of young girl diary young girl the diary of a young girl">
                  <div class="image">
                      <img class="nimg" src="./book_covers/085aa2c8.png" alt="The Diary of a Young Girl" />
                  </div>
                  <div class="content">
                      <h3>The Diary of a Young Girl</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="horror exorcist exorcisim an-exorcist">
                  <div class="image">
                      <img class="nimg" src="./book_covers/9f286545.png" alt="An-Exorcist" />
                  </div>
                  <div class="content">
                      <h3>An Exorcist Tells Why Hell Is Real</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="horror dracula vampire dracula">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a0126546.png" alt="dracula" />
                  </div>
                  <div class="content">
                      <h3>Dracula</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="horror haunt haunting hill house house haunting-of-hill-house">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a1f23e95.png" alt="haunting-of-hill-house" />
                  </div>
                  <div class="content">
                      <h3>The Haunting of Hill House</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="horror hell house house hell hell-house">
                  <div class="image">
                      <img class="nimg" src="./book_covers/a6602be0.png" alt="hell-house" />
                  </div>
                  <div class="content">
                      <h3>Hell House</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="horror pet sematary pet sematary- Pet-Sematary">
                  <div class="image">
                      <img class="nimg" src="./book_covers/9e62a067.png" alt="Pet-Sematary" />
                  </div>
                  <div class="content">
                      <h3>Pet Sematary</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="mystery hell heaven angel and demons demons angels angels_and_demons">
                  <div class="image">
                      <img class="nimg" src="./book_covers/d960d7b6.png" alt="angels_and_demons" />
                  </div>
                  <div class="content">
                      <h3>Angels and Demons</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="mystery gone girl gone girll Gone Girl">
                  <div class="image">
                      <img class="nimg" src="./book_covers/f7a15b07.png" alt="Gone Girl" />
                  </div>
                  <div class="content">
                      <h3>Gone Girl</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="mystery sher sherlock lock sher-lock">
                  <div class="image">
                      <img class="nimg" src="./book_covers/7a84a872.png" alt="sher-lock" />
                  </div>
                  <div class="content">
                      <h3>The Adventures of Sherlock Holmes</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="mystery silent patient the silent patient">
                  <div class="image">
                      <img class="nimg" src="./book_covers/cbbac0b5.png" alt="The Silent Patient" />
                  </div>
                  <div class="content">
                      <h3>The Silent Patient</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="mystery girl dragon tattoo the girl with the dragon tattoo">
                  <div class="image">
                      <img class="nimg" src="./book_covers/0322986e.png" alt="the girl with the dragon tattoo" />
                  </div>
                  <div class="content">
                      <h3>The Girl with the Dragon Tattoo</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="religion bhagavad gita bhagvad geeta hindi ancient learning bhagavad-gita-hindi">
                  <div class="image">
                      <img class="nimg" src="./book_covers/7fb21f4e.png" alt="Bhagavad-gita-hindi" />
                  </div>
                  <div class="content">
                      <h3>Bhagavad Gita</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="religion mahabharata mahabharat shree krishna krishn krishna mahabharat">
                  <div class="image">
                      <img class="nimg" src="./book_covers/4d375988.png" alt="mahabharat" />
                  </div>
                  <div class="content">
                      <h3>Mahabharata</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="religion manu smriti manu smriti">
                  <div class="image">
                      <img class="nimg" src="./book_covers/bb04fa2c.png" alt="manu smriti" />
                  </div>
                  <div class="content">
                      <h3>Manu Smriti</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="religion valmiki ramayana shrimad hindi shrimad valmiki ramayana">
                  <div class="image">
                      <img class="nimg" src="./book_covers/d8048dab.png" alt="Shrimad Valmiki Ramayana (Hindi Edition) - Valmiki" />
                  </div>
                  <div class="content">
                      <h3>Shrimad Valmiki Ramayana</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="religion vishnu hindi sahasranama stotram vishnu-sahasranama-stotram-hindi-387">
                  <div class="image">
                      <img class="nimg" src="./book_covers/8d7bdb3a.png" alt="vishnu-sahasranama-stotram-hindi-387" />
                  </div>
                  <div class="content">
                      <h3>Vishnu Sahasranama</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
               <a href="#new" class="swiper-slide box" data-tags="history 1776 david mccullough 1776 - david mccullough">
                  <div class="image">
                      <img class="nimg" src="./book_covers/8ae93cda.png" alt="1776 - David McCullough" />
                  </div>
                  <div class="content">
                      <h3>1776</h3>
                      <div class="price">₹ 149.00 <span>₹. 199.00</span></div>
                      <div class="stars">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                      </div>
                  </div>
              </a>
          </div>
      </div>
    
    </section>
    <!-- New Book End -->

    <!-- Review Start -->
    <section class="reviews" id="reviews">
      <h1>client's reviews</h1>
      <div class="swiper reviews-slider">
        <div class="swiper-wrapper">
          <div class="swiper-slide box">
            <i class="fas fa-quote-left quote"></i>
            <p>
              I stumbled upon Udhhan when I was in a reading slump, and it completely reignited my passion for books. The curated lists introduced me to authors and genres I never would have discovered otherwise.
            </p>
            <div class="content">
              <div class="info">
                <div class="name">Anthony karmen</div>
                <div class="job">Web Dev</div>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
              <div class="image">
                <img src="./img/avatar4.png" alt="" />
              </div>
            </div>
          </div>
          <div class="swiper-slide box">
            <i class="fas fa-quote-left quote"></i>
            <p>
              This site is a game-changer for book lovers! I love how easy it is to discover new reads and track my progress. The recommendations are spot-on, and the community features make it feel like home. Highly recommend!
            </p>
            <div class="content">
              <div class="info">
                <div class="name">Windah Basu</div>
                <div class="job">YouTuber</div>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
              <div class="image">
                <img src="./img/avatar2.svg" alt="" />
              </div>
            </div>
          </div>

          <div class="swiper-slide box">
            <i class="fas fa-quote-left quote"></i>
            <p>
              Udhhan is a haven for ebook lovers! Great selection, easy to use, and I love the personalized recommendations. Definitely check it out!
            </p>
            <div class="content">
              <div class="info">
                <div class="name">Umar S</div>
                <div class="job">Programmer</div>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
              <div class="image">
                <img src="./img/avatar5.svg" alt="" />
              </div>
            </div>
          </div>
          <div class="swiper-slide box">
            <i class="fas fa-quote-left quote"></i>
            <p>
            I found this e-book site incredibly useful for my freelance work. The selection is diverse, and the interface is user-friendly. A great resource for any professional!
            </p>
            <div class="content">
              <div class="info">
                <div class="name">Benjanim Foltz</div>
                <div class="job">Freelancer</div>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
              <div class="image">
                <img src="./img/avatar3.svg" alt="" />
              </div>
            </div>
          </div>

          <div class="swiper-slide box">
            <i class="fas fa-quote-left quote"></i>
            <p>
            As a doctor, my time is valuable. This site provides a quick and easy way to access the resources I need. I highly recommend it for its efficiency and comprehensive collection.
            </p>
            <div class="content">
              <div class="info">
                <div class="name">Kristina Bellis</div>
                <div class="job">Doctor</div>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
              <div class="image">
                <img src="./img/avatar6.svg" alt="" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Review End -->
    
   <!-- Member Start -->
  <!-- <section id="member" class="member">
    <div class="container">
      <h1>BECOME A MEMBER!</h1>
      <div class="row">
        <div class="content">
          <h3>
            Join Our <span>Member</span> And<br />
            Get Notify For <span>New Updates!</span>
          </h3>
          <p>
            Memberships are the perfect blend of a learning platform and a networking group. When interviewed about subscription models, business leaders representing retail, finances, and other industries, believe they hold the key to business growth.


          </p>
          <p>
            If you think about the communities of your early ancestors, you see how a group could flourish when they worked together as a team. If a person was ejected from their village, the prospects of survival were slim. Being alone was a weakness.
          </p>
          <form action="">
            <input type="email" name="" placeholder="Enter your email..." id="" class="box" required/>
            <input type="submit" value="get notify" class="btn" />
            <a href="./member.html" class="btn">Join Member</a>
          </form>
        </div>
        <div class="image">
          <img src="./img/img5.svg" alt="" />
        </div>
      </div>
    </div>
  </section>
  -->
 <section id="member" class="member">
        <div class="container">
              
                <h1>BECOME A MEMBER!</h1>
                <div class="row">
                    <div class="content">
                        <h3>
                            Join Our <span>Member</span> And<br />
                            Get Notify For <span>New Updates!</span>
                        </h3>
                        <p>
                            Memberships are the perfect blend of a learning platform and a networking group. When interviewed about subscription models, business leaders representing retail, finances, and other industries, believe they hold the key to business growth.
                        </p>
                        <p>
                            If you think about the communities of your early ancestors, you see how a group could flourish when they worked together as a team. If a person was ejected from their village, the prospects of survival were slim. Being alone was a weakness.
                        </p>
                        <form action="">
                            <input type="email" name="" placeholder="Enter your email..." id="" class="box" required/>
                            <input type="submit" value="get notify" class="btn" />
                            <a href="./register.php" class="btn">Join Member</a>
                        </form>
                    </div>
                    <div class="image">
                        <img src="./img/img5.svg" alt="" />
                    </div>
                </div>
           
        </div>
    </section>
  <!-- Member End -->
    <!-- Blogs Start -->
    <section class="blogs" id="blogs">
      <div class="container">
        <h1 class="heading"><span>our daily posts</span></h1>

        <div class="box-container">
          <div class="box">
            <div class="image">
              <img src="./img/blog1.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12th jan, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog3.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 10th Feb, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog2.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 28th Feb, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog4.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog5.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog6.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog7.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog8.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="image">
              <img src="./img/blog9.jpg" alt="" />
            </div>
            <div class="content">
              <h3>blog title goes here</h3>
              <p>
                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quod,
                adipisci!
              </p>
              <a href="./404Error.html" class="btn">read more</a>
              <div class="icons">
                <span> <i class="fas fa-calendar"></i> 12st sep, 2025 </span>
                <span> <i class="fas fa-user"></i> by admin </span>
              </div>
            </div>
          </div>
        </div>
        <div id="load-more">load more</div>
      </div>
    </section>
    <!-- Blogs End -->

    <!-- Footer Start -->
    <footer class="footer" id="footer">
      <div class="main-content">
        <div class="left box">
          <h2>About us</h2>
          <div class="content">
            <p>
              In the heart of India, where wisdom has been passed down for generations, lies a story of 
              perseverance and education. Inspired by the teachings of great scholars, our mission is to 
              make knowledge accessible to all. Like the legendary Nalanda University, which attracted 
              learners from all over the world, our bookstore aims to provide resources that ignite young 
              minds and fuel their thirst for learning.
            </p>
            <p>
              From the sacred scriptures of the Vedas to modern innovations in AI and technology, 
              education remains the strongest pillar of progress. We are committed to bringing you 
              books that enlighten, inspire, and empower the next generation of leaders.
            </p>
            
            <div class="social">
              <a href="#footer"><span class="fab fa-facebook-f"></span></a>
              <a href="#footer"><span class="fab fa-twitter"></span></a>
              <a href="#footer"><span class="fab fa-instagram"></span></a>
              <a href="#footer"><span class="fa-brands fa-youtube"></span></a>
            </div>
          </div>
        </div>
        <div class="center box">
          <h2>Address</h2>
          <div class="content">
            <div class="place">
              <span class="fas fa-map-marker-alt"></span>
              <span class="text">Uttar Pradesh, India</span>
            </div>
            <div class="phone">
              <span class="fas fa-phone-alt"></span>
              <span class="text">+91 6600669900</span>
            </div>
            <div class="email">
              <span class="fas fa-envelope"></span>
              <span class="text" style="text-transform: lowercase;">udhhan-india@edu.com</span>
            </div>
          </div>
        </div>
        <div class="right box">
          <h2>Contact us</h2>
          <div class="content">
            <form action="">
              <div class="email">
                <div class="text">Email</div>
                <input type="email"placeholder="Email address..." required />
              </div>
              <div class="msg">
                <div class="text">Message</div>
                <input type="text" placeholder="Your message..." required /></input>
              </div>
              <div class="btn">
                <button type="submit">Send</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="bottom">
          <span class="credit"
            >Copyright <span class="far fa-copyright"></span> 2025</span>
          <span> <a href="./index.html">Guys at UU</a> | All rights reserved.</span>
      </div>
    </footer>
    <!-- Footer End  -->

  <!-- Project Script -->
    <script src="./js/cart-favorites.js"></script>
    <script src="./js/search.js"></script>
    <!-- <script src="./js/popup.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="./js/script.js"></script>
  </body>
</html>
