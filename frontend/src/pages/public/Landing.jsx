import { Link } from 'react-router-dom';
import { ArrowRightIcon, SparklesIcon, TruckIcon, HeartIcon } from '@heroicons/react/24/outline';
import Button from '../../components/ui/Button';

const features = [
  { icon: SparklesIcon, title: 'Fresh Daily', desc: 'All meals cooked fresh every day using quality ingredients.' },
  { icon: TruckIcon, title: 'Fast Delivery', desc: 'Delivered hot to your door within selected areas.' },
  { icon: HeartIcon, title: 'Made with Love', desc: 'Family recipes passed down and perfected with passion.' },
];

export default function Landing() {
  return (
    <>
      {/* Hero */}
      <section className="relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <div className="container-app flex flex-col items-center py-16 sm:py-20 lg:flex-row lg:py-28">
          <div className="max-w-xl text-center lg:text-left">
            <h1 className="font-heading text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl lg:text-6xl">
              Welcome to <br />
              <span className="text-primary-600">Tonishen's Kitchen</span>
            </h1>
            <p className="mt-4 text-base text-stone-600 sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
              Homemade Filipino flavors made with passion. Order now and enjoy your favorite dishes delivered right to your doorstep.
            </p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
              <Link to="/menu">
                <Button size="lg" className="w-full sm:w-auto">
                  Order Now <ArrowRightIcon className="h-4 w-4" />
                </Button>
              </Link>
              <Link to="/about">
                <Button variant="outline" size="lg" className="w-full sm:w-auto">
                  Our Story
                </Button>
              </Link>
            </div>
          </div>

          {/* Hero illustration placeholder */}
          <div className="mt-12 lg:mt-0 lg:ml-12 flex-1 flex justify-center">
            <div className="relative h-64 w-64 sm:h-80 sm:w-80 lg:h-96 lg:w-96 rounded-full bg-gradient-to-br from-primary-200 to-accent-200 flex items-center justify-center">
              <span className="font-heading text-6xl sm:text-7xl lg:text-8xl">🍲</span>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-16 sm:py-20">
        <div className="container-app">
          <div className="text-center mb-12">
            <h2 className="font-heading text-3xl font-bold text-stone-900 sm:text-4xl">Why Choose Us?</h2>
            <p className="mt-2 text-stone-500 max-w-2xl mx-auto">Every dish is a labor of love from our family to yours.</p>
          </div>
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
            {features.map((f) => (
              <div key={f.title} className="group rounded-xl border border-stone-200 bg-white p-6 text-center shadow-sm transition-all hover:shadow-md hover:-translate-y-1">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                  <f.icon className="h-6 w-6" />
                </div>
                <h3 className="mt-4 text-lg font-semibold text-stone-900">{f.title}</h3>
                <p className="mt-2 text-sm text-stone-500 leading-relaxed">{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-stone-900 py-16 sm:py-20">
        <div className="container-app text-center">
          <h2 className="font-heading text-3xl font-bold text-white sm:text-4xl">Hungry? Order Now!</h2>
          <p className="mt-3 text-stone-400 max-w-lg mx-auto">Browse our menu and get your favorite Filipino dishes delivered fresh and hot.</p>
          <Link to="/menu" className="mt-8 inline-block">
            <Button size="lg">View Our Menu <ArrowRightIcon className="h-4 w-4" /></Button>
          </Link>
        </div>
      </section>
    </>
  );
}
