<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- other head tags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-rich-text::styles theme="richtextlaravel" data-turbo-track="false" />
</head>

<body class="font-sans antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="flex-1 min-h-screen p-6 bg-gray-50 overflow-y-auto">
            {{ $slot ?? '' }}
        </main>
    </div>

    <script>
        // Listen for the trix-attachment-add event (it bubbles)
        document.addEventListener("trix-attachment-add", function(event) {
            console.log(event);
            return upload(event);
        });

        function upload (event) {
            if (! event?.attachment?.file) return

            const form = new FormData()
            form.append('attachment', event.attachment.file)

            const options = {
                method: 'POST',
                body: form,
                headers: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content,
                }
            }

            fetch('/attachments', options)
                .then(resp => resp.json())
                .then((data) => {
                    event.attachment.setAttributes({
                        url: data.image_url,
                        href: data.image_url,
                    })
                })
        }
    </script>
</body>

</html>