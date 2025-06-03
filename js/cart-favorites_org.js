// // Helper function to set a cookie
// function setCookie(name, value, days) {
//     let expires = "";
//     if (days) {
//         const date = new Date();
//         date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
//         expires = "; expires=" + date.toUTCString();
//     }
//     document.cookie = name + "=" + encodeURIComponent(value || "") + expires + "; path=/";
// }

// // Helper function to get a cookie
// function getCookie(name) {
//     const nameEQ = name + "=";
//     const ca = document.cookie.split(';');
//     for (let i = 0; i < ca.length; i++) {
//         let c = ca[i];
//         while (c.charAt(0) === ' ') c = c.substring(1, c.length);
//         if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
//     }
//     return null;
// }

// // Helper function to erase a cookie
// function eraseCookie(name) {
//     document.cookie = name + '=; Max-Age=-99999999;';
// }

// // Safely parse JSON with fallback to empty array
// function safelyParseJSON(jsonString) {
//     try {
//         return JSON.parse(jsonString || '[]');
//     } catch (e) {
//         console.error("Error parsing JSON:", e);
//         return [];
//     }
// }

// // Add to cart with notification
// function addToCart(identifier) {
//     try {
//         let cart = safelyParseJSON(getCookie('cart'));
//         if (!cart.includes(identifier)) {
//             cart.push(identifier);
//             setCookie('cart', JSON.stringify(cart), 7);
//             console.log(`Added item to cart: ${identifier}`);
//             alert(`Item added to cart!`);
//             return true;
//         } else {
//             console.log(`Item already in cart: ${identifier}`);
//             alert("This item is already in your cart");
//             return false;
//         }
//     } catch (e) {
//         console.error("Error in addToCart:", e);
//         // Reset the cart if corrupt
//         setCookie('cart', JSON.stringify([identifier]), 7);
//         alert(`Item added to cart!`);
//         return true;
//     }
// }

// // Add to favorites with notification
function addToFavorites(identifier) {
    try {
        let favorites = safelyParseJSON(getCookie('favorites'));
        if (!favorites.includes(identifier)) {
            favorites.push(identifier);
            setCookie('favorites', JSON.stringify(favorites), 7);
            console.log(`Added item to favorites: ${identifier}`);
            alert(`Item added to favorites!`);
            return true;
        } else {
            console.log(`Item already in favorites: ${identifier}`);
            alert("This item is already in your favorites");
            return false;
        }
    } catch (e) {
        console.error("Error in addToFavorites:", e);
        // Reset favorites if corrupt
        setCookie('favorites', JSON.stringify([identifier]), 7);
        alert(`Item added to favorites!`);
        return true;
    }
}

// // Display cart items with loading indicator
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
        gridContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(220px, 1fr))';
        gridContainer.style.gap = '15px';
        favoritesContainer.appendChild(gridContainer);

        favoriteBooks.forEach(book => {
            const bookDiv = document.createElement('div');
            bookDiv.className = 'favorite-book-item';
            bookDiv.style.border = '3px solid #ddd';
            bookDiv.style.borderRadius = '8px';
            bookDiv.style.padding = '10px';
            bookDiv.style.display = 'flex';
            bookDiv.style.flexDirection = 'column';
            bookDiv.style.alignItems = 'center';
            bookDiv.style.textAlign = 'center';

            const coverImg = document.createElement('img');
            coverImg.src = book.img?.book_cover_1 || './img/placeholder.png';
            coverImg.alt = book.title || 'No Title';
            coverImg.style.border = '3px solid #ddd';
            coverImg.style.borderRadius = '5px';
            coverImg.style.maxWidth = '120px';
            coverImg.style.height = '150px';
            coverImg.style.marginBottom = '10px';

            const titleHeading = document.createElement('h4');
            titleHeading.textContent = book.title || 'No Title';
            titleHeading.style.marginBottom = '5px';

            const authorParagraph = document.createElement('p');
            authorParagraph.textContent = `By ${book.author || 'Unknown Author'}`;
            authorParagraph.style.fontSize = '1.25em';
            authorParagraph.style.fontWeight = '600';
            authorParagraph.style.color = '#555';
            authorParagraph.style.marginBottom = '8px';

            const priceParagraph = document.createElement('p');
            priceParagraph.textContent = `Price: ${book.price}`;
            priceParagraph.style.fontSize = '1.25em';
            priceParagraph.style.fontWeight = '600';
            priceParagraph.style.color = '#555';
            priceParagraph.style.marginBottom = '10px';


            const addToCartButton = document.createElement('button');
            addToCartButton.textContent = 'Add to Cart';
            addToCartButton.className = 'btn';
            addToCartButton.style.backgroundColor = '#386c5c';
            addToCartButton.style.color = 'white';
            addToCartButton.style.border = 'none';
            addToCartButton.style.padding = '8px 15px';
            addToCartButton.style.borderRadius = '5px';
            addToCartButton.style.cursor = 'pointer';
            addToCartButton.style.fontSize = '1.45em';
            addToCartButton.style.fontWeight = '500';
            addToCartButton.style.marginTop = '3px';
            addToCartButton.onclick = () => popcart(book.id); // Use popcart for consistency

            
            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.className = 'btn';
            removeButton.style.backgroundColor = '#f44336';
            removeButton.style.color = 'white';
            removeButton.style.border = 'none';
            removeButton.style.padding = '8px 15px';
            removeButton.style.borderRadius = '5px';
            removeButton.style.cursor = 'pointer';
            removeButton.style.fontSize = '1.45em';
            removeButton.style.fontWeight = '500';
            removeButton.style.marginTop ='10px';
            removeButton.style.marginBottom ='10px';
            removeButton.onclick = () => removeFromFavorites(book.id); // Use book.id


            bookDiv.appendChild(coverImg);
            bookDiv.appendChild(titleHeading);
            bookDiv.appendChild(authorParagraph);
            bookDiv.appendChild(priceParagraph);
            bookDiv.appendChild(addToCartButton);
            bookDiv.appendChild(removeButton);
            gridContainer.appendChild(bookDiv);
        });

    } catch (error) {
        console.error("Error displaying favorites:", error);
        favoritesContainer.innerHTML = '<p>There was an error loading your favorites.</p>';
    }
}

document.addEventListener('DOMContentLoaded', displayFavorites);

// Make sure you have the loadBookIndex() function in your script:
async function loadBookIndex() {
    try {
        const response = await fetch('book_index.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Error loading book index:", error);
        return null;
    }
}

function removeFromFavorites(bookId) {
    try {
        let favorites = safelyParseJSON(getCookie('favorites'));
        favorites = favorites.filter(id => id !== bookId); // Filter out the ID to remove
        setCookie('favorites', JSON.stringify(favorites), 7);
        displayFavorites(); // Refresh the displayed favorites
        console.log(`Removed book with ID ${bookId} from favorites.`);
    } catch (error) {
        console.error("Error removing from favorites:", error);
    }
}

// Also ensure you have the safelyParseJSON, getCookie, addToFavorites, and removeFromFavorites (adjusted to work with book IDs) functions.
// async function displayCart() {
//     const cartContainer = document.getElementById('cart-items');
//     if (!cartContainer) return;
    
//     // Show loading indicator
//     cartContainer.innerHTML = '<p>Loading your cart items...</p>';
    
//     try {
//         const cart = safelyParseJSON(getCookie('cart'));
//         let totalPrice = 0;

//         if (cart.length === 0) {
//             cartContainer.innerHTML = '<p>Your cart is empty.</p>';
//             return;
//         }

//         // Clear container for new items
//         cartContainer.innerHTML = '';
        
//         // Add a checkout button container at the top
//         const checkoutContainer = document.createElement('div');
//         checkoutContainer.className = 'checkout-container';
//         checkoutContainer.style.cssText = 'margin-bottom: 20px; text-align: right;';
//         checkoutContainer.innerHTML = `<button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem;">Proceed to Checkout</button>`;
//         cartContainer.appendChild(checkoutContainer);

//         for (const identifier of cart) {
//             try {
//                 const response = await fetch(`https://www.googleapis.com/books/v1/volumes?q=isbn:${encodeURIComponent(identifier)}`);
//                 const data = await response.json();
                
//                 if (data.items && data.items.length > 0) {
//                     const book = data.items[0].volumeInfo;
//                     const saleInfo = data.items[0].saleInfo;
                    
//                     const bookElement = document.createElement('div');
//                     bookElement.className = 'cart-item';
//                     bookElement.style.cssText = `
//                         display: flex;
//                         align-items: center;
//                         margin-bottom: 20px;
//                         padding: 15px;
//                         border-radius: 8px;
//                         box-shadow: 0 2px 8px rgba(0,0,0,0.1);
//                         background: white;
//                     `;
                    
//                     // Calculate price
//                     let price = 'Unavailable';
//                     let priceValue = 0;
                    
//                     if (saleInfo?.listPrice?.amount) {
//                         price = `₹${saleInfo.listPrice.amount}`;
//                         priceValue = saleInfo.listPrice.amount;
//                     } else if (saleInfo?.retailPrice?.amount) {
//                         price = `₹${saleInfo.retailPrice.amount}`;
//                         priceValue = saleInfo.retailPrice.amount;
//                     } else if (saleInfo?.saleability === 'FREE') {
//                         price = 'Free';
//                     }
                    
//                     if (priceValue) {
//                         totalPrice += priceValue;
//                     }
                    
//                     bookElement.innerHTML = `
//                         <img src="${book.imageLinks?.thumbnail || 'placeholder_image.png'}" alt="${book.title}" 
//                              style="height: 150px; width: auto; margin-right: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
//                         <div style="flex-grow: 1;">
//                             <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 1.4rem;">${book.title}</h3>
//                             <p style="margin: 5px 0; color: #555;">Author: ${book.authors?.join(', ') || 'Unknown'}</p>
//                             <p style="margin: 5px 0; font-weight: bold; color: #e63946;">${price}</p>
//                             <p style="margin: 5px 0; font-size: 0.9rem;">Publisher: ${book.publisher || 'Unknown'}</p>
//                             <button onclick="removeFromCart('${identifier}')" 
//                                     style="background: #f8f9fa; color: #333; border: 1px solid #ddd; padding: 5px 10px; 
//                                     border-radius: 4px; cursor: pointer; margin-top: 5px;">Remove</button>
//                         </div>
//                     `;
//                     cartContainer.appendChild(bookElement);
//                 }
//             } catch (error) {
//                 console.error('Error fetching book:', error);
                
//                 // Create error item
//                 const errorElement = document.createElement('div');
//                 errorElement.className = 'cart-item error';
//                 errorElement.style.cssText = 'padding: 10px; background: #fff3f3; border-radius: 5px; margin-bottom: 10px;';
//                 errorElement.innerHTML = `
//                     <p>Error loading item (${identifier})</p>
//                     <button onclick="removeFromCart('${identifier}')">Remove</button>
//                 `;
//                 cartContainer.appendChild(errorElement);
//             }
//         }

//         // Add total and checkout
//         const totalElement = document.createElement('div');
//         totalElement.className = 'cart-total';
//         totalElement.style.cssText = `
//             margin-top: 20px;
//             padding: 15px;
//             background: #f8f9fa;
//             border-radius: 8px;
//             font-size: 1.2rem;
//             display: flex;
//             justify-content: space-between;
//             align-items: center;
//         `;
//         totalElement.innerHTML = `
//             <div>
//                 <p style="margin: 0; font-weight: bold;">Total: ₹${totalPrice.toFixed(2)}</p>
//                 <p style="margin: 5px 0 0; font-size: 0.9rem; color: #666;">Items: ${cart.length}</p>
//             </div>
//             <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem;">Checkout</button>
//         `;
//         cartContainer.appendChild(totalElement);
//     } catch (e) {
//         console.error("Error displaying cart:", e);
//         cartContainer.innerHTML = '<p>There was an error loading your cart. Please try refreshing the page.</p>';
//         // Reset corrupt cart
//         setCookie('cart', '[]', 7);
//     }
// }

// // Display favorites with improved styling
// async function displayFavorites() {
//     const favoritesContainer = document.getElementById('favorite-items');
//     if (!favoritesContainer) return;
    
//     favoritesContainer.innerHTML = '<p>Loading your favorites...</p>';
    
//     try {
//         const favorites = safelyParseJSON(getCookie('favorites'));

//         if (favorites.length === 0) {
//             favoritesContainer.innerHTML = '<p>Your favorites list is empty.</p>';
//             return;
//         }

//         favoritesContainer.innerHTML = '';
        
//         // Create grid layout for favorites
//         const grid = document.createElement('div');
//         grid.style.cssText = `
//             display: grid;
//             grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
//             gap: 20px;
//             margin-top: 20px;
//         `;
//         favoritesContainer.appendChild(grid);

//         for (const identifier of favorites) {
//             try {
//                 const response = await fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(identifier)}`);
//                 const data = await response.json();
                
//                 if (data.items && data.items.length > 0) {
//                     const book = data.items[0].volumeInfo;
//                     const bookElement = document.createElement('div');
//                     bookElement.className = 'favorite-item';
//                     bookElement.style.cssText = `
//                         display: flex;
//                         flex-direction: column;
//                         padding: 15px;
//                         border-radius: 8px;
//                         box-shadow: 0 2px 8px rgba(0,0,0,0.1);
//                         background: white;
//                         height: 100%;
//                     `;
                    
//                     // Truncate description
//                     const description = book.description || 'No description available';
//                     const truncatedDesc = description.length > 200 
//                         ? description.substring(0, 200) + '...' 
//                         : description;
                    
//                     bookElement.innerHTML = `
//                         <div style="display: flex; margin-bottom: 15px;">
//                             <img src="${book.imageLinks?.thumbnail || 'placeholder_image.png'}" alt="${book.title}" 
//                                  style="height: 150px; width: auto; margin-right: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
//                             <div>
//                                 <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 1.4rem;">${book.title}</h3>
//                                 <p style="margin: 5px 0; color: #555;">Author: ${book.authors?.join(', ') || 'Unknown'}</p>
//                                 <p style="margin: 5px 0; font-size: 0.9rem;">Publisher: ${book.publisher || 'Unknown'}</p>
//                             </div>
//                         </div>
//                         <p style="margin: 0 0 15px; flex-grow: 1;">${truncatedDesc}</p>
//                         <div style="display: flex; justify-content: space-between;">
//                             <button onclick="removeFromFavorites('${identifier}')" 
//                                     style="background: #f8f9fa; color: #333; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
//                                 Remove
//                             </button>
//                             <button onclick="addToCart('${identifier}')" class="btn" style="padding: 5px 15px;">
//                                 Add to Cart
//                             </button>
//                         </div>
//                     `;
//                     grid.appendChild(bookElement);
//                 }
//             } catch (error) {
//                 console.error('Error fetching book:', error);
                
//                 // Create error item
//                 const errorElement = document.createElement('div');
//                 errorElement.className = 'favorite-item error';
//                 errorElement.style.cssText = 'padding: 10px; background: #fff3f3; border-radius: 5px;';
//                 errorElement.innerHTML = `
//                     <p>Error loading item (${identifier})</p>
//                     <button onclick="removeFromFavorites('${identifier}')">Remove</button>
//                 `;
//                 grid.appendChild(errorElement);
//             }
//         }
//     } catch (e) {
//         console.error("Error displaying favorites:", e);
//         favoritesContainer.innerHTML = '<p>There was an error loading your favorites. Please try refreshing the page.</p>';
//         // Reset corrupt favorites
//         setCookie('favorites', '[]', 7);
//     }
// }

// // Function to remove a book from the cart
// function removeFromCart(identifier) {
//     try {
//         let cart = safelyParseJSON(getCookie('cart'));
//         cart = cart.filter(item => item !== identifier);
//         setCookie('cart', JSON.stringify(cart), 7);
//         displayCart(); // Refresh the cart display
//     } catch (e) {
//         console.error("Error removing from cart:", e);
//         // Reset corrupt cart
//         setCookie('cart', '[]', 7);
//         displayCart();
//     }
// }

// // Function to remove a book from favorites
// function removeFromFavorites(identifier) {
//     try {
//         let favorites = safelyParseJSON(getCookie('favorites'));
//         favorites = favorites.filter(item => item !== identifier);
//         setCookie('favorites', JSON.stringify(favorites), 7);
//         displayFavorites(); // Refresh the favorites display
//     } catch (e) {
//         console.error("Error removing from favorites:", e);
//         // Reset corrupt favorites
//         setCookie('favorites', '[]', 7);
//         displayFavorites();
//     }
// }

// Popular cart pop
// cart-favorites.js

// --- Functions for Adding Items from Popular Section to Cart ---

// Function to add a book from the popular section to the shopping cart (only ID)
async function popcart(id) {
    try {
        let shoppingCart = safelyParseJSON(getCookie('popShoppingCart'));
        if (!shoppingCart.includes(id)) {
            shoppingCart.push(id);
            setCookie('popShoppingCart', JSON.stringify(shoppingCart), 7);
            console.log(`Added to pop cart: ID - ${id}`);
            alert(`Item added to your shopping cart!`);
            return true;
        } else {
            console.log(`Item already in pop cart: ID - ${id}`);
            alert("This item is already in your shopping cart");
            return false;
        }
    } catch (error) {
        console.error("Error in popcart:", error);
        // Reset the cart if corrupt
        setCookie('popShoppingCart', JSON.stringify([id]), 7);
        alert(`Item added to your shopping cart!`);
        return true;
    }
}

// --- Functions to Display Cart Items from Popular Section Data ---

// Display shopping cart items from popular section data
async function displayCart() {
    const cartContainer = document.getElementById('cart-items');
    if (!cartContainer) return;

    cartContainer.innerHTML = '<p>Loading your shopping cart items...</p>';

    try {
        const shoppingCart = safelyParseJSON(getCookie('popShoppingCart'));
        const bookIndex = await loadBookIndex();
        let totalPrice = 0;

        if (shoppingCart.length === 0) {
            cartContainer.innerHTML = '<p>Your shopping cart is empty.</p>';
            return;
        }

        cartContainer.innerHTML = '';

        const checkoutContainer = document.createElement('div');
        checkoutContainer.className = 'checkout-container';
        checkoutContainer.style.cssText = 'margin-bottom: 20px; text-align: right;';
        // checkoutContainer.innerHTML = `<button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem; font-weight: 500;">Proceed to Checkout</button>`;
        checkoutContainer.innerHTML = `<div style="text-align: right; margin-top: 20px;">  <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem; font-weight: 500;" id="proceed-to-checkout">Proceed to Checkout</button>  </div>`;
        
        cartContainer.appendChild(checkoutContainer);

        document.getElementById('proceed-to-checkout').addEventListener('click', function() {
            window.location.href = './checkout.php';
          });
        for (const itemId of shoppingCart) {
            const book = bookIndex?.find(b => b.id === itemId);
            if (book) {
                const bookElement = document.createElement('div');
                bookElement.className = 'cart-item';
                bookElement.style.cssText = `
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    padding: 15px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    background: white;
                `;

                const imgPath = book.img?.book_cover_1 || 'placeholder_image.png';
                const priceValue = parseFloat(book.price?.replace('₹', '').replace('.00', '').trim()) || 0;
                totalPrice += priceValue;

                bookElement.innerHTML = `
                    <img src="${imgPath}" alt="${book.title}"
                         style="height: 150px; width: auto; margin-right: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                    <div style="flex-grow: 1;">
                        <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 1.4rem;">${book.title}</h3>
                        <p style="margin: 5px 0; color: #555;">Author: ${book.author || 'Unknown'}</p>
                        <p style="margin: 5px 0; font-weight: bold; color: #e63946;">₹${book.price || 'Unavailable'}</p>
                        <button onclick="removePopCartItem('${itemId}')"
                                style="background: #f8f9fa; color: #333; border: 1px solid #ddd; padding: 5px 10px;
                                       border-radius: 4px; cursor: pointer; margin-top: 5px;">Remove</button>
                    </div>
                `;
                cartContainer.appendChild(bookElement);
            } else {
                const errorElement = document.createElement('div');
                errorElement.className = 'cart-item error';
                errorElement.innerHTML = `<p>Error loading item (ID: ${itemId})</p>
                                            <button onclick="removePopCartItem('${itemId}')">Remove</button>`;
                cartContainer.appendChild(errorElement);
            }
        }

        const totalElement = document.createElement('div');
        totalElement.className = 'cart-total';
        totalElement.style.cssText = `
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        totalElement.innerHTML = `
            <div>
                <p style="margin: 0; font-weight: bold;">Total: ₹${totalPrice.toFixed(2)}</p>
                <p style="margin: 5px 0 0; font-size: 0.9rem; color: #666;">Items: ${shoppingCart.length}</p>
            </div>
            <button class="btn" style="padding: 0.6rem 1.5rem; font-size: 1.4rem; font-weight: 600;" id="proceed-to-checkout">Checkout</button>
        `;
        cartContainer.appendChild(totalElement);
        document.getElementById('proceed-to-checkout').addEventListener('click', function() {
            window.location.href = './checkout.php';
          });
    } catch (error) {
        console.error("Error displaying popular shopping cart:", error);
        cartContainer.innerHTML = '<p>There was an error loading your shopping cart. Please try refreshing the page.</p>';
        setCookie('popShoppingCart', '[]', 7);
    }
}

// --- Function to Remove Items from Popular Cart ---

// Function to remove an item from the popular shopping cart
function removePopCartItem(id) {
    try {
        let shoppingCart = safelyParseJSON(getCookie('popShoppingCart'));
        shoppingCart = shoppingCart.filter(item => item !== id);
        setCookie('popShoppingCart', JSON.stringify(shoppingCart), 7);
        displayCart(); // Refresh the cart display
    } catch (error) {
        console.error("Error removing from popular shopping cart:", error);
        setCookie('popShoppingCart', '[]', 7);
        displayCart();
    }
}

// --- Existing Helper Functions (No Changes Needed) ---

// Safely parse JSON with fallback to empty array
function safelyParseJSON(jsonString) {
    try {
        return JSON.parse(jsonString || '[]');
    } catch (e) {
        console.error("Error parsing JSON:", e);
        return [];
    }
}

// Helper function to set a cookie
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + encodeURIComponent(value || "") + expires + "; path=/";
}

// Helper function to get a cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

// Helper function to erase a cookie
function eraseCookie(name) {
    document.cookie = name + '=; Max-Age=-99999999;';
}

// Load book index (assuming it's in the same directory)
// async function loadBookIndex() {
//     try {
//         const response = await fetch('book_index.json');
//         if (!response.ok) {
//             throw new Error(`HTTP error! status: ${response.status}`);
//         }
//         return await response.json();
//     } catch (error) {
//         console.error("Error loading book index:", error);
//         return null;
//     }
// }

// --- Example Usage in HTML (Popular Section) ---

// You would update your onclick handlers in the popular books section like this:

// <a href="#populer" class="btn add-cart-btn" onclick="popcart('9112a9bf')">Add To Cart</a>

// --- The display function is called in the <script> tag in cart.html ---