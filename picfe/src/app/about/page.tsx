export default function AboutPage() {
  return (
    <main style={{ maxWidth: 800, margin: '40px auto', padding: 20 }}>
      <h1>About PictureThis</h1>
      <p>
        PictureThis helps you generate images from prompts. This is a minimal
        about page added to resolve 404s for <code>/about</code>.
      </p>
      <p>
        For terms and privacy, see the links in the footer below.
      </p>
      <footer style={{ marginTop: 40, borderTop: '1px solid #eee', paddingTop: 16 }}>
        <a href="/terms">Terms &amp; Conditions</a>
        {' — '}
        <a href="/privacy">Privacy Policy</a>
      </footer>
    </main>
  );
}
