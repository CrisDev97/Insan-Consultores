<section id="contacto" class="contact-section" aria-label="Contacto">
  <div class="contact-container">

    <div class="contact-head">
      <h2>Contacto</h2>
      <p>Envíanos un mensaje y te responderemos lo antes posible.</p>
    </div>

    @if(session('contact_success'))
      <div class="contact-alert">
        {{ session('contact_success') }}
      </div>
    @endif

    <div class="contact-grid">

      <div class="contact-card">
        <h3 class="contact-card-title">Escríbenos</h3>

        <form method="POST" action="{{ route('contact.store') }}" class="contact-form" novalidate>
          @csrf

          <div class="field">
            <label class="label" for="name">Nombre</label>
            <input class="input" id="name" name="name" type="text" value="{{ old('name') }}" required>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label class="label" for="email">Correo</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label class="label" for="phone">Teléfono (opcional)</label>
            <input class="input" id="phone" name="phone" type="text" value="{{ old('phone') }}">
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label class="label" for="message">Mensaje</label>
            <textarea class="textarea" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
            @error('message') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <button class="btn btn-primary" type="submit" class="contact-btn">
            Enviar mensaje
          </button>
        </form>
      </div>

      <aside class="contact-info">
        <div class="info-box">
          <div class="info-title">Canales directos</div>

          <div class="info-item">
            <span class="info-ico">✉️</span>
            <div>
              <div class="info-label">Correo</div>
              <div class="info-value">info@tudominio.com</div>
            </div>
          </div>

          <div class="info-item">
            <span class="info-ico">📞</span>
            <div>
              <div class="info-label">WhatsApp</div>
              <div class="info-value">+51 999 999 999</div>
            </div>
          </div>

          <a class="btn btn-amber" target="_blank" rel="noopener"
             href="https://wa.me/51999999999?text=Hola%2C%20quiero%20informaci%C3%B3n%20sobre%20asesor%C3%ADa%20vocacional">
            Hablar por WhatsApp →
          </a>

          <div class="info-note">
            Atención con enfoque humano y profesional, orientada a resultados.
          </div>
        </div>
      </aside>

    </div>
  </div>
</section>
