const backgrounds = [
    'url("../images/paystubs1.png")',
    'url("../images/paystubs2.jpg")',
    'url("../images/paystubs3.jpg")',
    'url("../images/paystubs4.jpg")'
];

// Select a random background image
const randomIndex = Math.floor(Math.random() * backgrounds.length);
const backgroundDiv = document.querySelector('.background');
backgroundDiv.style.backgroundImage = backgrounds[randomIndex];