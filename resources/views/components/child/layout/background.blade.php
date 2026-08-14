<div class="absolute inset-0 -z-10 overflow-hidden">

    {{-- Base Gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br {{ $gradient() }}"></div>

    {{-- Aurora Glow --}}
    <div
        class="absolute -top-40 -left-40
               h-[30rem] w-[30rem]
               rounded-full
               bg-white/20
               blur-3xl">
    </div>

    <div
        class="absolute bottom-0 right-0
               h-[28rem] w-[28rem]
               rounded-full
               bg-cyan-300/20
               blur-3xl">
    </div>

    {{-- Decorative Circles --}}
    <div
        class="absolute top-24 left-12
               h-24 w-24
               rounded-full
               bg-white/30
               blur-xl">
    </div>

    <div
        class="absolute right-20 top-40
               h-32 w-32
               rounded-full
               bg-indigo-200/20
               blur-xl">
    </div>

    <div
        class="absolute bottom-28 left-1/3
               h-20 w-20
               rounded-full
               bg-cyan-200/20
               blur-xl">
    </div>

</div>