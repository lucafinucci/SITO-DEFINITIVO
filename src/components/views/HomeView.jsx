import Hero from '../sections/Hero';
import Problem from '../sections/Problem';
import Solution from '../sections/Solution';
import Modules from '../sections/Modules';
import Sectors from '../sections/Sectors';
import WhyUs from '../sections/WhyUs';
import About from '../sections/About';
import CustomerArea from '../sections/CustomerArea';
import Contact from '../sections/Contact';

export default function HomeView() {
    return (
        <>
            <Hero />
            <Problem />
            <Solution />
            <Modules />
            <Sectors />
            <WhyUs />
            <About />
            <CustomerArea />
            <Contact />
        </>
    );
}
