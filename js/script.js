function displayPassword() {
  const passwordInput = document.getElementById("password");
  const displayEye = document.getElementById("display-pass");
  const hideEye = document.getElementById("hiden-pass");

  if (passwordInput.type === "password") {
      passwordInput.type = "text";
      displayEye.style.display = "none";
      hideEye.style.display = "inline-block";
  } else {
      passwordInput.type = "password";
      displayEye.style.display = "inline-block";
      hideEye.style.display = "none";
  }
}

function displayPasswordConfirm() {
  const passwordConfirmInput = document.getElementById("passwordConfirm");
  const displayEyeConfirm = document.getElementById("display-passConfirm");
  const hideEyeConfirm = document.getElementById("hiden-passConfirm");

  if (passwordConfirmInput.type === "password") {
      passwordConfirmInput.type = "text";
      displayEyeConfirm.style.display = "none";
      hideEyeConfirm.style.display = "inline-block";
  } else {
      passwordConfirmInput.type = "password";
      displayEyeConfirm.style.display = "inline-block";
      hideEyeConfirm.style.display = "none";
  }
}
  
  searchForm = document.querySelector(".search-form");
  
  // document.querySelector("#search-btn").onclick = () => {
  //   searchForm.classList.toggle("active");
  // };
  const searchBtn = document.querySelector("#search-btn");
  if (searchBtn) {
      searchBtn.onclick = () => {
          searchForm.classList.toggle("active");
      };
  } else {
      console.error("Search button element not found!");
  }
  var navLinks = document.querySelectorAll("header .navbar a");
  var section = document.querySelectorAll("section");
  
  window.onscroll = () => {
    searchForm.classList.remove("active");
  
    section.forEach((sec) => {
      var top = window.scrollY;
      var height = sec.offsetHeight;
      var offset = sec.offsetTop - 150;
      var id = sec.getAttribute("id");
  
      if (top >= offset && top < offset + height) {
        navLinks.forEach((links) => {
          links.classList.remove("active");
          document
            .querySelector("header .navbar a[href *= " + id + "]")
            .classList.add("active");
        });
      }
    });
  
    if (window.scrollY > 80) {
      document.querySelector(".header .header-2").classList.add("active");
    } else {
      document.querySelector(".header .header-2").classList.remove("active");
    }
  };
  function loader() {
    document.querySelector(".loader-container").classList.add("active");
  }
  function fadeOut() {
    setTimeout(loader, 4000);
  }
  window.onload = () => {
    if (window.scrollY > 80) {
      document.querySelector(".header .header-2").classList.add("active");
    } else {
      document.querySelector(".header .header-2").classList.remove("active");
    }
    fadeOut();
  };
  
  var swiper = new Swiper(".books-slider", {
    loop: true,
    centeredSlides: true,
    autoplay: {
      delay: 2000,
      disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
  
  var swiper = new Swiper(".populer-slider", {
    spaceBetween: 10,
    loop: true,
    centeredSlides: true,
    autoplay: {
      delay: 3100,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      450: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 3,
      },
      1024: {
        slidesPerView: 4,
      },
    },
  });
  
  var swiper = new Swiper(".new-slider", {
    spaceBetween: 10,
    loop: true,
    centeredSlides: true,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
  
  var swiper = new Swiper(".new-slider-2", {
    spaceBetween: 10,
    loop: true,
    centeredSlides: true,
    autoplay: {
      delay: 3250,
      disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
  
  var swiper = new Swiper(".reviews-slider", {
    spaceBetween: 10,
    grabCursor: true,
    loop: true,
    centeredSlides: true,
    autoplay: {
      delay: 11500,
      disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
  
  let loadMoreBtn = document.querySelector("#load-more");
  let currentItem = 3;
  
  loadMoreBtn.onclick = () => {
    let boxes = [...document.querySelectorAll(".container .box-container .box")];
    for (var i = currentItem; i < currentItem + 3; i++) {
      boxes[i].style.display = "inline-block";
    }
    currentItem += 3;
  
    if (currentItem >= boxes.length) {
      loadMoreBtn.style.display = "none";
    }
  };
  