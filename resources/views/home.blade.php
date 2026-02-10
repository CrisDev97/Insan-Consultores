@extends('layouts.site')

@section('content')

<section id="inicio" class="hero">
  <div class="carousel" data-carousel="root" aria-label="Carrusel principal">

    <div class="carousel-track" data-carousel="track">
      @forelse($banners as $b)
        <div class="carousel-slide" data-carousel="slide">
          <img src="{{ asset('storage/'.$b->image_path) }}" alt="{{ $b->name }}">
          <div class="carousel-overlay"></div>

          <div class="carousel-content">
            <div class="carousel-inner">
              <h1 class="carousel-title">{{ $b->name }}</h1>
              <p class="carousel-subtitle">
                Orientación vocacional con enfoque profesional, cálido y humano.
              </p>
              <a class="carousel-cta" href="#contacto">
                Solicitar información <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="carousel-slide" data-carousel="slide">
          <img src="https://via.placeholder.com/1920x900?text=Sin+banners" alt="Sin banners">
          <div class="carousel-overlay"></div>
          <div class="carousel-content">
            <div class="carousel-inner">
              <h1 class="carousel-title">Tu asesoría vocacional</h1>
              <p class="carousel-subtitle">Registra banners en el portal admin para mostrarlos aquí.</p>
              <a class="carousel-cta" href="#contacto">Solicitar información →</a>
            </div>
          </div>
        </div>
      @endforelse
    </div>

    @if($banners->count() > 1)
      <button class="carousel-btn carousel-btn--prev" type="button" data-carousel="prev" aria-label="Anterior">
        ‹
      </button>
      <button class="carousel-btn carousel-btn--next" type="button" data-carousel="next" aria-label="Siguiente">
        ›
      </button>
      <div class="carousel-dots" data-carousel="dots" aria-label="Paginación del carrusel"></div>
    @endif

  </div>
</section>



<section id="nosotros" class="about-light">
  <div class="about-light__container">

    <div class="about-light__head">
      <h2 class="about-light__title">Nosotros</h2>
      <p class="about-light__subtitle">
        Contribuimos a que las personas tomen decisiones vocacionales con claridad, confianza y un plan accionable.
      </p>
    </div>

    <div class="mvv">
      <article class="mvv__card">
        <div class="mvv__icon" aria-hidden="true">🎯</div>
        <h3 class="mvv__title">Misión</h3>
        <p class="mvv__text">
          Proporcionar soluciones precisas e innovadoras en nuestro campo de especialización, maximizando las habilidades
          de las personas y/o de los estudiantes, realizando procesos de orientación vocacional altamente precisas,
          elevando la competitividad de los futuros profesionales y de los egresados, con diagnósticos y asesorías
          especializadas e innovadoras.
        </p>
      </article>

      <article class="mvv__card">
        <div class="mvv__icon" aria-hidden="true">🌟</div>
        <h3 class="mvv__title">Visión</h3>
        <p class="mvv__text">
          Ser la primera institución más especializado en el país y en la región, en el desarrollo de la vocación y del
          talento, innovando en tecnología y metodologías, garantizando siempre la calidad de nuestros servicios. Como
          los líderes en el rubro del desarrollo de la vocación y el talento apuntamos a brindar aportes constantes para
          el cumplimiento de los retos y desafíos de la educación en este milenio.
        </p>
      </article>

      <article class="mvv__card">
        <div class="mvv__icon" aria-hidden="true">🤝</div>
        <h3 class="mvv__title">Valores</h3>

        <ul class="mvv__list">
          <li>Precisión en los resultados</li>
          <li>Alta calidad</li>
          <li>Innovación</li>
          <li>Orientación al servicio</li>
        </ul>
      </article>
    </div>

  </div>
</section>


<section id="servicios" class="services-section">
  <div class="services-container">

    <div class="services-head">
      <div>
        <h2>Servicios</h2>
        <p>Conoce nuestras opciones de asesoría vocacional.</p>
      </div>
    </div>

    <div class="services-grid">
      @forelse($services as $s)
        <article class="service-card">
          <div class="service-img">
            @if($s->image_1_path)
              <img src="{{ asset('storage/'.$s->image_1_path) }}" alt="{{ $s->title }}">
            @endif
          </div>

          <div class="service-body">
            <h3>{{ $s->title }}</h3>
            <p>{{ \Illuminate\Support\Str::limit($s->description, 90) }}</p>

            <div class="service-tags">
              @if($s->sessions_count)
                <span class="service-tag service-tag--sessions">{{ $s->sessions_count }} sesiones</span>
              @endif

              @if(!is_null($s->price))
                <span class="service-tag service-tag--price">S/ {{ number_format((float)$s->price, 2) }}</span>
              @endif
            </div>

            <button type="button"
                    class="service-btn"
                    data-modal="serviceModal{{ $s->id }}">
              Ver detalle
            </button>
          </div>
        </article>

        {{-- MODAL --}}
        <div id="serviceModal{{ $s->id }}" class="modal-overlay">
          <div class="modal-card" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" data-close="serviceModal{{ $s->id }}">✕</button>

            <div class="modal-grid">
              <div>
                <h3 style="margin:0 0 8px; font-size:20px; color:#111827;">{{ $s->title }}</h3>
                <p style="margin:0 0 10px; color:#6b7280; line-height:1.6;">{{ $s->description }}</p>

                @if(is_array($s->includes) && count($s->includes))
                  <h4 style="margin:12px 0 6px; font-size:14px; color:#111827;">Lo que incluye</h4>
                  <ul style="margin:0; padding-left:18px; color:#374151; line-height:1.7;">
                    @foreach($s->includes as $item)
                      <li>{{ $item }}</li>
                    @endforeach
                  </ul>
                @endif

                @if(is_array($s->objectives) && count($s->objectives))
                  <h4 style="margin:12px 0 6px; font-size:14px; color:#111827;">Objetivos</h4>
                  <ul style="margin:0; padding-left:18px; color:#374151; line-height:1.7;">
                    @foreach($s->objectives as $item)
                      <li>{{ $item }}</li>
                    @endforeach
                  </ul>
                @endif
              </div>

              <div class="modal-side">
                @if($s->image_1_path)
                  <img src="{{ asset('storage/'.$s->image_1_path) }}" alt="Imagen 1">
                @endif
                @if($s->image_2_path)
                  <img src="{{ asset('storage/'.$s->image_2_path) }}" alt="Imagen 2">
                @endif

                <div class="modal-box">
                  <div class="modal-row">
                    <div class="modal-label">Sesiones</div>
                    <div class="modal-value">{{ $s->sessions_count ?? '—' }}</div>
                  </div>

                  <div class="modal-row" style="margin-bottom:0;">
                    <div class="modal-label">Costo</div>
                    <div class="modal-value">
                      {{ is_null($s->price) ? '—' : 'S/ '.number_format((float)$s->price, 2) }}
                    </div>
                  </div>

                  <a href="#contacto" class="modal-cta" data-close="serviceModal{{ $s->id }}">
                    Quiero información
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>

      @empty
        <div style="color:#6b7280;">Aún no hay servicios disponibles.</div>
      @endforelse
    </div>

  </div>
</section>


@include('profile.partials.site-clients')

@include('profile.partials.site-contact')

@endsection
