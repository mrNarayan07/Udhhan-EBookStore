/**
 * cart-favorites.js
 *
 * Handles:
 * 1. FAVORITES using 'favorites' Cookie and data from book_index.json.
 * 2. POPULAR section cart ('popShoppingCart' Cookie) using data from book_index.json.
 *
 * NOTE: The main combined cart display on cart.php is primarily rendered server-side using PHP
 *       to access both PHP session data and cookie data. This JS file provides helper
 *       functions needed by other pages (like index.php) and the 'Remove' functionality
 *       for cookie-based items on cart.php.
 */

// --- Helper Functions (Cookies, JSON Parsing, Book Index Loading) ---

function safelyParseJSON(jsonString) {
    try {
        return JSON.parse(jsonString || '[]');
    } catch (e) {
        console.error("Error parsing JSON:", e, "Input:", jsonString);
        return [];
    }
}

function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    const stringValue = (typeof value === 'string') ? value : JSON.stringify(value);
    document.cookie = name + "=" + encodeURIComponent(stringValue) + expires + "; path=/; SameSite=Lax";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) {
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; Max-Age=-99999999; path=/; SameSite=Lax';
}

async function loadBookIndex() {
    try {
        const response = await fetch('./book_index.json'); // Adjust path if needed
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Error loading book index:", error);
        return null;
    }
}

// --- Favorites Management Functions ---

function addToFavorites(bookId) {
    // ... (same as previous version - handles 'favorites' cookie) ...
     if (!bookId) return false;
    try {
        let favorites = safelyParseJSON(getCookie('favorites'));
        if (!Array.isArray(favorites)) favorites = [];
        if (!favorites.includes(bookId)) {
            favorites.push(bookId);
            setCookie('favorites', JSON.stringify(favorites), 30);
            alert(`Item added to favorites!`);
            return true;
        } else {
            alert("This item is already in your favorites.");
            return false;
        }
    } catch (e) {
        console.error("Error in addToFavorites:", e);
        setCookie('favorites', JSON.stringify([bookId]), 30); // Recovery attempt
        alert(`Item added to favorites!`);
        return true;
    }
}

function removeFromFavorites(bookId) {
     // ... (same as previous version - handles 'favorites' cookie) ...
     if (!bookId) return;
     try {
        let favorites = safelyParseJSON(getCookie('favorites'));
        if (!Array.isArray(favorites)) favorites = [];
        const initialLength = favorites.length;
        favorites = favorites.filter(id => id !== bookId);
        if (favorites.length < initialLength) {
             setCookie('favorites', JSON.stringify(favorites), 30);
             console.log(`Removed book ${bookId} from favorites.`);
             alert("Item removed from favorites.");
             // Refresh display ONLY if on the favorites page
             if (document.getElementById('favorite-items')) {
                 displayFavorites();
             }
        }
    } catch (error) {
        console.error("Error removing from favorites:", error);
    }
}

// async function displayFavorites() {
//     // ... (same as previous version - displays items from 'favorites' cookie) ...
//      const favoritesContainer = document.getElementById('favorite-items');
//     if (!favoritesContainer) return;
//     favoritesContainer.innerHTML = '<p class="text-center text-gray-500">Loading favorites...</p>';
//      try {
//         const favorites = safelyParseJSON(getCookie('favorites'));
//         if (!Array.isArray(favorites) || favorites.length === 0) {
//             favoritesContainer.innerHTML = '<p class="text-center text-gray-500">Favorites list is empty.</p>';
//             return;
//         }
//         const bookIndex = await loadBookIndex();
//         if (!bookIndex) {
//              favoritesContainer.innerHTML = '<p class="text-center text-red-500">Error loading book data.</p>';
//              return;
//         }
//         const favoriteBooks = bookIndex.filter(book => book && book.id && favorites.includes(book.id.toString()));
//         if (favoriteBooks.length === 0) {
//             favoritesContainer.innerHTML = '<p class="text-center text-gray-500">No favorite books found.</p>';
//             return;
//         }
//          favoritesContainer.innerHTML = ''; // Clear loading
//          const gridContainer = document.createElement('div');
//         gridContainer.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4';
//         favoritesContainer.appendChild(gridContainer);

//         favoriteBooks.forEach(book => {
//             // ... (create bookDiv, img, title, author, price elements as before) ...
//              const bookDiv = document.createElement('div');
//             bookDiv.className = 'favorite-book-item bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center border border-gray-200';
//              // Image
//              const coverImg = document.createElement('img');
//             coverImg.src = book.img?.book_cover_1 || './img/placeholder.png';
//             coverImg.alt = book.title || 'Book Cover';
//             coverImg.className = 'w-32 h-48 object-cover mb-3 rounded shadow';
//              // Title
//              const titleHeading = document.createElement('h4');
//             titleHeading.textContent = book.title || 'No Title';
//             titleHeading.className = 'text-md font-semibold mb-1 text-gray-800';
//              // Author
//              const authorParagraph = document.createElement('p');
//             authorParagraph.textContent = `By ${book.author || 'Unknown Author'}`;
//             authorParagraph.className = 'text-sm text-gray-600 mb-2';
//              // Price
//              const priceParagraph = document.createElement('p');
//              const priceText = book.price ? `Price: ${book.price}` : 'Price: N/A';
//             priceParagraph.textContent = priceText;
//             priceParagraph.className = 'text-sm font-medium text-gray-700 mb-3';

//             // Add to Cart Button (Needs careful implementation if used here)
//             // const addToCartButton = document.createElement('button');
//             // addToCartButton.textContent = 'Add to Cart';
//             // addToCartButton.className = 'btn bg-blue-500 ...';
//             // addToCartButton.onclick = () => { /* Call function to add to PHP SESSION cart */ };

//             // Remove from Favorites Button
//             const removeButton = document.createElement('button');
//             removeButton.textContent = 'Remove';
//             removeButton.className = 'btn bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1 px-3 rounded mt-2 w-full';
//             removeButton.onclick = () => removeFromFavorites(book.id.toString());

//              bookDiv.appendChild(coverImg);
//             bookDiv.appendChild(titleHeading);
//             bookDiv.appendChild(authorParagraph);
//             bookDiv.appendChild(priceParagraph);
//             // bookDiv.appendChild(addToCartButton);
//             bookDiv.appendChild(removeButton);
//             gridContainer.appendChild(bookDiv);
//         });

//      } catch (error) {
//         console.error("Error displaying favorites:", error);
//         favoritesContainer.innerHTML = '<p class="text-center text-red-500">Error loading favorites.</p>';
//     }
// }
async function displayFavorites() {
    const favoritesContainer = document.getElementById('favorite-items');
    if (!favoritesContainer) return;

    favoritesContainer.innerHTML = '<p>Loading your favorites...</p>';

    try {
        const favorites = safelyParseJSON(getCookie('favorites'));

        if (favorites.length === 0) {
            favoritesContainer.innerHTML = '<p>Your favorites list is empty.</p>';
            return;
        }

        favoritesContainer.innerHTML = ''; // Clear loading message

        const bookIndex = await loadBookIndex(); // Assuming you have this function

        if (!bookIndex) {
            favoritesContainer.innerHTML = '<p>Error loading book data.</p>';
            return;
        }

        const favoriteBooks = bookIndex.filter(book => favorites.includes(book.id));

        if (favoriteBooks.length === 0) {
            favoritesContainer.innerHTML = '<p>No favorite books found.</p>';
            return;
        }

        // Create a grid layout for the favorite items
        const gridContainer = document.createElement('div');
        gridContainer.style.display = 'grid';
        gridContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(250px, 1fr))';
        gridContainer.style.gap = '20px';
        favoritesContainer.appendChild(gridContainer);

        favoriteBooks.forEach(book => {
            const bookDiv = document.createElement('div');
            bookDiv.className = 'favorite-book-item';
            bookDiv.style.border = '1px solid #ddd';
            bookDiv.style.borderRadius = '8px';
            bookDiv.style.padding = '15px';
            bookDiv.style.display = 'flex';
            bookDiv.style.flexDirection = 'column';
            bookDiv.style.alignItems = 'center';
            bookDiv.style.textAlign = 'center';

            const coverImg = document.createElement('img');
            coverImg.src = book.img?.book_cover_1 || './img/placeholder.png';
            coverImg.alt = book.title || 'No Title';
            coverImg.style.maxWidth = '150px';
            coverImg.style.height = 'auto';
            coverImg.style.marginBottom = '10px';

            const titleHeading = document.createElement('h4');
            titleHeading.textContent = book.title || 'No Title';
            titleHeading.style.marginBottom = '5px';

            const authorParagraph = document.createElement('p');
            authorParagraph.textContent = `By ${book.author || 'Unknown Author'}`;
            authorParagraph.style.fontSize = '0.9em';
            authorParagraph.style.color = '#555';
            authorParagraph.style.marginBottom = '10px';

            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.className = 'btn';
            removeButton.style.backgroundColor = '#f44336';
            removeButton.style.color = 'white';
            removeButton.style.border = 'none';
            removeButton.style.padding = '8px 15px';
            removeButton.style.borderRadius = '5px';
            removeButton.style.cursor = 'pointer';
            removeButton.style.fontSize = '0.9em';
            removeButton.onclick = () => removeFromFavorites(book.id); // Use book.id

            const addToCartButton = document.createElement('button');
            addToCartButton.textContent = 'Add to Cart';
            addToCartButton.className = 'btn';
            addToCartButton.style.backgroundColor = '#4CAF50';
            addToCartButton.style.color = 'white';
            addToCartButton.style.border = 'none';
            addToCartButton.style.padding = '8px 15px';
            addToCartButton.style.borderRadius = '5px';
            addToCartButton.style.cursor = 'pointer';
            addToCartButton.style.fontSize = '0.9em';
            addToCartButton.style.marginTop = '5px';
            addToCartButton.onclick = () => popcart(book.id); // Use popcart for consistency

            bookDiv.appendChild(coverImg);
            bookDiv.appendChild(titleHeading);
            bookDiv.appendChild(authorParagraph);
            bookDiv.appendChild(removeButton);
            bookDiv.appendChild(addToCartButton);
            gridContainer.appendChild(bookDiv);
        });

    } catch (error) {
        console.error("Error displaying favorites:", error);
        favoritesContainer.innerHTML = '<p>There was an error loading your favorites.</p>';
    }
}

document.addEventListener('DOMContentLoaded', displayFavorites);
// --- Popular Section Cart ('popShoppingCart' Cookie) Functions ---

/**
 * Adds a book ID (from book_index.json) to the 'popShoppingCart' cookie.
 * Used by index.php popular section.
 * @param {string} id The book ID.
 * @returns {boolean}
 */
async function popcart(id) {
    if (!id) return false;
    try {
        let shoppingCart = safelyParseJSON(getCookie('popShoppingCart'));
        if (!Array.isArray(shoppingCart)) shoppingCart = [];

        if (!shoppingCart.includes(id)) {
            shoppingCart.push(id);
            setCookie('popShoppingCart', JSON.stringify(shoppingCart), 7);
            console.log(`Added to pop cart: ID - ${id}`);
            alert(`Item added to your shopping cart!`); // Feedback for index.php interaction
            return true;
        } else {
            console.log(`Item already in pop cart: ID - ${id}`);
            alert("This item is already in your shopping cart.");
            return false;
        }
    } catch (error) {
        console.error("Error in popcart:", error);
        setCookie('popShoppingCart', JSON.stringify([id]), 7); // Recovery attempt
        alert(`Item added to your shopping cart!`);
        return true;
    }
}

/**
 * Removes an item from the 'popShoppingCart' cookie.
 * This function WILL BE CALLED by buttons on the cart.php page for cookie-sourced items.
 * @param {string} id The book ID to remove.
 */
function removePopCartItem(id) {
    if (!id) return;
    try {
        let shoppingCart = safelyParseJSON(getCookie('popShoppingCart'));
        if (!Array.isArray(shoppingCart)) shoppingCart = [];

        const initialLength = shoppingCart.length;
        shoppingCart = shoppingCart.filter(item => item !== id);

        if (shoppingCart.length < initialLength) {
            setCookie('popShoppingCart', JSON.stringify(shoppingCart), 7);
            console.log(`Removed item ${id} from pop cart.`);
            // IMPORTANT: Reload the cart page to reflect the change,
            // as PHP rendered the initial combined list.
            window.location.reload();
            // alert("Item removed from cart."); // Optional feedback, reload is clearer
        } else {
             console.log(`Item ${id} not found in pop cart.`);
        }
    } catch (error) {
        console.error("Error removing from popular shopping cart:", error);
        // Attempt recovery
        // eraseCookie('popShoppingCart');
        // window.location.reload();
    }
}

/**
 * Displays cart items based ONLY on the 'popShoppingCart' cookie.
 * This function is likely used by other pages (e.g., maybe a mini-cart display).
 * It is NOT responsible for rendering the main combined cart on cart.php.
 */
async function displayCart() {
    const cartContainer = document.getElementById('cart-items'); // Target element ID
    // Check if we are on cart.php - if so, PHP handles initial load, so maybe skip this.
    // However, it might be used elsewhere, so keep it, but ensure cart.php doesn't call it on load.
    if (!cartContainer) {
        // console.log("Element with ID 'cart-items' not found, skipping displayCart().");
        return; // Exit if the target container doesn't exist
    }
    // Avoid overwriting PHP-rendered content on cart.php if called unexpectedly
    if (cartContainer.dataset.renderedBy === 'php') {
         console.log("Cart already rendered by PHP, skipping JS displayCart().");
         return;
    }


    cartContainer.innerHTML = '<p>Loading your shopping cart items...</p>'; // Loading message

    try {
        const shoppingCartIds = safelyParseJSON(getCookie('popShoppingCart'));
        if (!Array.isArray(shoppingCartIds)) {
             throw new Error("Invalid cart data in cookie.");
        }

        let totalPrice = 0;

        if (shoppingCartIds.length === 0) {
            cartContainer.innerHTML = '<p>Your shopping cart is empty.</p>';
            // Note: This message might be misleading on cart.php if session items exist.
            return;
        }

        const bookIndex = await loadBookIndex();
        if (!bookIndex) {
             cartContainer.innerHTML = '<p>Error loading book data.</p>';
             return;
        }

        cartContainer.innerHTML = ''; // Clear loading message

        // Optional: Add Checkout button (might be duplicated if PHP adds one too)
        // const checkoutContainer = document.createElement('div');
        // ... setup checkout button ...
        // cartContainer.appendChild(checkoutContainer);

        for (const itemId of shoppingCartIds) {
            const book = bookIndex.find(b => b && b.id === itemId);
            if (book) {
                const bookElement = document.createElement('div');
                bookElement.className = 'cart-item'; // Use styles from cart.php CSS
                bookElement.style.cssText = `display: flex; align-items: center; margin-bottom: 20px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;`; // Apply base styles

                const imgPath = book.img?.book_cover_1 || './img/placeholder.png';
                // Attempt to parse price, handle '₹' and potential '.00'
                let priceValue = 0;
                if (book.price && typeof book.price === 'string') {
                     priceValue = parseFloat(book.price.replace(/[₹,]/g, '').trim()) || 0;
                }
                totalPrice += priceValue;
                const displayPrice = book.price ? `₹${priceValue.toFixed(2)}` : 'Price Unavailable';


                bookElement.innerHTML = `
                    <img src="${imgPath}" alt="${book.title || 'Book'}" style="height: 100px; width: auto; margin-right: 15px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="flex-grow: 1;">
                        <h3 style="margin-top: 0; margin-bottom: 5px; font-size: 1.2rem;">${book.title || 'No Title'}</h3>
                        <p style="margin: 2px 0; color: #555; font-size: 0.9rem;">Author: ${book.author || 'Unknown'}</p>
                        <p style="margin: 2px 0; font-weight: bold; color: #e63946; font-size: 1rem;">${displayPrice}</p>
                        <!-- This button calls the JS function to remove from COOKIE -->
                        <button onclick="removePopCartItem('${itemId}')"
                                style="background: #f8f9fa; color: #333; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-top: 8px; font-size: 0.9rem;">
                            Remove
                        </button>
                    </div>
                `;
                cartContainer.appendChild(bookElement);
            } else {
                // Handle case where book ID from cookie isn't found in book_index.json
                const errorElement = document.createElement('div');
                errorElement.className = 'cart-item error';
                errorElement.innerHTML = `<p style="color: red; padding: 10px;">Error loading item (ID: ${itemId}) - Data not found.</p>
                                           <button onclick="removePopCartItem('${itemId}')" style="...">Remove</button>`;
                cartContainer.appendChild(errorElement);
            }
        }

        // Display Total (Only for cookie items if this function runs)
        const totalElement = document.createElement('div');
        totalElement.className = 'cart-total'; // Use styles from cart.php
        totalElement.style.cssText = `margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 1.2rem; display: flex; justify-content: space-between; align-items: center;`;
        totalElement.innerHTML = `
            <div>
                <p style="margin: 0; font-weight: bold;">Subtotal: ₹${totalPrice.toFixed(2)}</p>
                <p style="margin: 5px 0 0; font-size: 0.9rem; color: #666;">Items: ${shoppingCartIds.length}</p>
            </div>
            <!-- <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem;">Checkout</button> -->
        `;
        cartContainer.appendChild(totalElement);

    } catch (error) {
        console.error("Error displaying shopping cart:", error);
        cartContainer.innerHTML = '<p>There was an error loading shopping cart items.</p>';
        // Avoid resetting cookie here unless sure it's corrupted.
    }
}

    // --- Google Books Cart ('googleBooksCart' Cookie) ---
function addGoogleBookToCart(bookDetails) { // For items from user.php recommendations
    if (!bookDetails || !bookDetails.isbn) {
        console.error("addGoogleBookToCart: Invalid book details", bookDetails);
        alert("Could not add item: invalid details.");
        return;
    }
    // Handle "Not For Sale" or similar status from the price string
    const nonSalePrices = ['Not Available', 'Not For Sale', 'Price Unavailable', 'N/A'];
    if (nonSalePrices.includes(bookDetails.price)) {
        alert(`"${bookDetails.title}" is currently not available for purchase.`);
        return;
    }

    try {
        let googleCart = safelyParseJSON(getCookie('googleBooksCart'), []);
        const existingItemIndex = googleCart.findIndex(item => item.isbn === bookDetails.isbn);

        let numericPrice = 0;
        if (bookDetails.price && typeof bookDetails.price === 'string') {
            const cleanedPrice = bookDetails.price.replace(/[₹,A-Za-z\s]/g, '').trim(); // Remove currency symbols, letters, spaces
            if (cleanedPrice === "" && bookDetails.price.toLowerCase() === "free") {
                numericPrice = 0;
            } else if (!isNaN(parseFloat(cleanedPrice))) {
                numericPrice = parseFloat(cleanedPrice);
            }
        } else if (typeof bookDetails.price === 'number') {
            numericPrice = bookDetails.price;
        }


        if (existingItemIndex > -1) {
            googleCart[existingItemIndex].quantity = (googleCart[existingItemIndex].quantity || 0) + 1;
        } else {
            googleCart.push({
                isbn: bookDetails.isbn,
                title: bookDetails.title || 'No Title',
                authors: bookDetails.authors || 'Unknown',
                image: bookDetails.image || './img/placeholder.png',
                price: bookDetails.price || 'N/A', // Original string
                numericPrice: numericPrice,
                currencyCode: bookDetails.currencyCode || 'INR',
                quantity: 1
            });
        }
        setCookie('googleBooksCart', googleCart, 7);
        alert(`"${bookDetails.title}" added to cart!`);
        updateCartIconCount();
        if (document.getElementById('cart-items')) displayCart(); // Refresh cart.html if open
    } catch (error) {
        console.error("Error in addGoogleBookToCart:", error);
        alert("An error occurred while adding the item to your cart.");
    }
}

function removeGoogleBookItem(isbn) { // Removes item from 'googleBooksCart'
    if (!isbn) return;
    try {
        let googleCart = safelyParseJSON(getCookie('googleBooksCart'), []);
        const initialLength = googleCart.length;
        googleCart = googleCart.filter(item => item.isbn !== isbn);
        if (googleCart.length < initialLength) {
            setCookie('googleBooksCart', googleCart, 7);
            alert("Item removed from cart.");
            updateCartIconCount();
            if (document.getElementById('cart-items')) displayCart(); // Refresh cart.html
        }
    } catch (error) { console.error("Error removing from Google Books cart:", error); }
}
// --- Event Listeners ---

// Display favorites automatically ONLY if the favorites container exists
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('favorite-items')) {
        displayFavorites();
    }

    // DO NOT automatically call displayCart() here if cart.php is handling the combined display.
    // It might be called by other pages if needed.
    // Example: If you have a mini-cart element with id="mini-cart-items", you might call:
    // if (document.getElementById('mini-cart-items')) { displayCart('mini-cart-items'); } // (displayCart needs modification to accept target ID)

     // Add event listener for the checkout button IF it's generated by PHP in cart.php
    const checkoutButton = document.getElementById('proceed-to-checkout-php'); // Give PHP button a specific ID
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            window.location.href = './checkout.php';
        });
    }

});

