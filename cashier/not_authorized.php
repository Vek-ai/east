<style>
  .unauthorized-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
    padding: 2rem;
    font-family: 'Segoe UI', sans-serif;
    color: white;
  }

  .auth-box {
    padding: 3rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    text-align: center;
    max-width: 450px;
    width: 100%;
    backdrop-filter: blur(10px);
    background: rgba(0, 0, 0, 0.3);
  }

  .auth-box i.fa-lock {
    font-size: 6rem;
    color: #ff0707ff;
    margin-bottom: 1.5rem;
  }

  .auth-box h1 {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
  }

  .auth-box p {
    font-size: 1rem;
    color: #e0e0e0;
  }

  .btn-home {
    margin-top: 2rem;
  }

  @media (max-width: 576px) {
    .auth-box {
      padding: 2rem 1rem;
    }

    .auth-box i.fa-lock {
      font-size: 4.5rem;
    }

    .auth-box h1 {
      font-size: 1.5rem;
    }
  }
</style>

<div class="unauthorized-wrapper">
  <div class="auth-box">
    <i class="fas fa-lock"></i>
    <h1>Access Denied</h1>
    <p>You don’t have permission to access this page.</p>
    <a href="?page=" class="btn btn-outline-light btn-home">
      <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
  </div>
</div>
