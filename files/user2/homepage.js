function scrollToAbout() {
  const aboutSection = document.getElementById("about-us")
  if (aboutSection) {
    aboutSection.scrollIntoView({ behavior: "smooth" })
  }
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: "smooth" })
}

// Explore button functionality
document.getElementById("explore-btn").addEventListener("click", () => {
  console.log("Explore button clicked!")
})
