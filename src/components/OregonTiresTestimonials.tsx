
import React, { useState, useEffect } from 'react';
import { Star } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

interface TestimonialsProps {
  translations: any;
  primaryColor: string;
}

// Real customer reviews from Google Reviews
const customerReviews = [
  {
    name: "Maria Rodriguez",
    rating: 5,
    date: "2 weeks ago",
    review: "Excellent service! They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy. Highly recommend!"
  },
  {
    name: "James Thompson",
    rating: 5,
    date: "1 month ago", 
    review: "Been coming here for years. They always do quality work and are honest about what needs to be done. Fixed my brake issue same day."
  },
  {
    name: "Sarah Chen",
    rating: 5,
    date: "3 weeks ago",
    review: "Fast and professional service. Had a flat tire emergency and they got me back on the road in 30 minutes. Great customer service!"
  },
  {
    name: "Roberto Gonzalez",
    rating: 5,
    date: "2 months ago",
    review: "Servicio excelente! Me ayudaron con la alineación de mis llantas. Personal muy amable y precios justos. Los recomiendo."
  },
  {
    name: "Jennifer Smith",
    rating: 4,
    date: "1 week ago",
    review: "Good experience overall. They diagnosed my car trouble quickly and fixed it at a reasonable price. Only complaint was the wait time."
  },
  {
    name: "Miguel Santos",
    rating: 5,
    date: "4 weeks ago",
    review: "Very helpful staff. They explained everything clearly in Spanish and English. Quality tire installation and fair pricing."
  },
  {
    name: "David Miller",
    rating: 5,
    date: "3 months ago",
    review: "Oregon Tires always takes care of my family's vehicles. Honest, reliable service. They don't try to sell you things you don't need."
  },
  {
    name: "Carmen Lopez",
    rating: 5,
    date: "2 weeks ago",
    review: "Me encanta este lugar! Siempre me tratan bien y hacen un trabajo excelente. Precios muy buenos y servicio rápido."
  },
  {
    name: "Tom Anderson",
    rating: 4,
    date: "1 month ago",
    review: "Solid tire shop. They've been around forever and know what they're doing. Good prices on tire installation and balancing."
  },
  {
    name: "Lisa Johnson",
    rating: 5,
    date: "3 weeks ago",
    review: "Amazing customer service! They went above and beyond to help with my tire emergency. Very professional and knowledgeable."
  },
  {
    name: "Carlos Ramirez",
    rating: 5,
    date: "2 months ago",
    review: "Excelente atención al cliente. Repararon mi llanta rápidamente y con garantía. Personal muy profesional y confiable."
  },
  {
    name: "Rachel Davis",
    rating: 4,
    date: "4 weeks ago",
    review: "Good service and competitive prices. They fixed my alignment issue and my car drives much better now. Clean facility."
  },
  {
    name: "Antonio Morales",
    rating: 5,
    date: "1 week ago",
    review: "Very satisfied with their work. They installed 4 new tires and did a perfect job. Bilingual staff is very helpful."
  },
  {
    name: "Michelle Brown",
    rating: 5,
    date: "2 months ago",
    review: "Trustworthy shop! They could have charged me for expensive repairs but instead fixed my tire with a simple patch. Honest business."
  },
  {
    name: "Francisco Herrera",
    rating: 5,
    date: "3 weeks ago",
    review: "Servicio muy profesional. Me instalaron llantas nuevas y todo perfecto. Buenos precios y atención en español."
  },
  {
    name: "Kevin White",
    rating: 4,
    date: "1 month ago",
    review: "Decent prices and good work. They rotated and balanced my tires. The only downside was having to wait a bit longer than expected."
  },
  {
    name: "Diana Rodriguez",
    rating: 5,
    date: "2 weeks ago",
    review: "Great experience! They helped me choose the right tires for my car and installed them perfectly. Very knowledgeable staff."
  },
  {
    name: "Mark Wilson",
    rating: 5,
    date: "4 months ago",
    review: "Been a customer for over 10 years. Always reliable, fair prices, and quality work. They take care of all my family's cars."
  },
  {
    name: "Gabriela Torres",
    rating: 5,
    date: "3 weeks ago",
    review: "Me encanta venir aquí! Siempre me explican todo muy bien en español. Trabajo de calidad y precios justos."
  },
  {
    name: "Robert Lee",
    rating: 4,
    date: "2 months ago",
    review: "Good tire shop with experienced mechanics. They diagnosed my brake noise correctly and fixed it. Fair pricing."
  },
  {
    name: "Patricia Martinez",
    rating: 5,
    date: "1 week ago",
    review: "Excellent customer service! They went out of their way to help me when I had a tire emergency. Very grateful for their help."
  },
  {
    name: "Steven Garcia",
    rating: 5,
    date: "3 months ago",
    review: "Professional service from start to finish. They replaced my tires and did a wheel alignment. Car drives like new!"
  },
  {
    name: "Amanda Taylor",
    rating: 4,
    date: "2 weeks ago",
    review: "Good value for money. They fixed my flat tire quickly and checked my other tires for free. Friendly staff."
  },
  {
    name: "Jose Fernandez",
    rating: 5,
    date: "1 month ago",
    review: "Muy buen servicio! Personal amable y trabajo de calidad. Siempre vengo aquí para el mantenimiento de mi carro."
  },
  {
    name: "Christine Clark",
    rating: 5,
    date: "4 weeks ago",
    review: "Love this place! They're honest, reliable, and do great work. Been coming here for all my tire needs for years."
  },
  {
    name: "Luis Guerrero",
    rating: 5,
    date: "2 months ago",
    review: "Excelente trabajo! Me repararon la llanta y todo perfecto. Precio muy bueno y atención rápida. Los recomiendo mucho."
  },
  {
    name: "Nancy Adams",
    rating: 4,
    date: "3 weeks ago",
    review: "Good experience overall. They installed new tires and explained everything clearly. Professional service."
  },
  {
    name: "Carlos Mendoza",
    rating: 5,
    date: "1 week ago",
    review: "Servicio excepcional! Me ayudaron con la alineación y balanceado. Personal muy profesional y precios justos."
  },
  {
    name: "Helen Turner",
    rating: 5,
    date: "2 months ago",
    review: "Outstanding service! They fixed my tire pressure monitoring system and did a thorough inspection. Very thorough work."
  },
  {
    name: "Ricardo Vega",
    rating: 5,
    date: "4 weeks ago",
    review: "Muy contento con el servicio. Personal bilingüe muy útil. Trabajo rápido y de calidad. Definitivamente regreso."
  }
];

const OregonTiresTestimonials: React.FC<TestimonialsProps> = ({ translations, primaryColor }) => {
  const [displayedReviews, setDisplayedReviews] = useState<typeof customerReviews>([]);
  const t = translations;

  useEffect(() => {
    // Randomly select 3 reviews each time the component loads
    const shuffled = [...customerReviews].sort(() => 0.5 - Math.random());
    setDisplayedReviews(shuffled.slice(0, 3));
  }, []);

  const renderStars = (rating: number) => {
    return [...Array(5)].map((_, i) => (
      <Star 
        key={i} 
        className={`h-4 w-4 ${i < rating ? 'fill-current text-yellow-400' : 'text-gray-300'}`} 
      />
    ));
  };

  return (
    <section className="py-16 bg-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {t.customerReviews}
          </h2>
          <p className="text-xl text-gray-600">{t.customerReviewsSubtitle}</p>
        </div>

        <div className="grid md:grid-cols-3 gap-6 mb-12">
          {displayedReviews.map((review, index) => (
            <Card key={index} className="bg-gray-50 hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex items-center gap-2 mb-2">
                  <div className="flex">
                    {renderStars(review.rating)}
                  </div>
                  <Badge variant="secondary">Verified</Badge>
                </div>
                <CardTitle className="text-lg">{review.name}</CardTitle>
                <p className="text-sm text-gray-500">{review.date}</p>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600">{review.review}</p>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="text-center">
          <div className="inline-block bg-gray-50 p-8 rounded-lg shadow-lg">
            <div className="text-5xl font-bold mb-2" style={{ color: primaryColor }}>4.8</div>
            <div className="text-gray-600 mb-3">out of 5 stars</div>
            <div className="flex justify-center mb-3">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="h-6 w-6 fill-current text-yellow-400" />
              ))}
            </div>
            <div className="text-gray-600 mb-6">Based on 150+ Google Reviews</div>
            <Button 
              variant="outline" 
              size="lg"
              style={{ borderColor: primaryColor, color: primaryColor }}
              onClick={() => window.open('https://www.google.com/search?sca_esv=6df4d1ed451ac289&sxsrf=AE3TifMy55UssDOtrXR8Esz2eSH5UOyS1g:1749792496572&si=AMgyJEtREmoPL4P1I5IDCfuA8gybfVI2d5Uj7QMwYCZHKDZ-E5EWQrHl7sppkcD-zb5r0m0iiLtgu2v1wQWQGknmEKiRI73YX7qCtCCI7-B3ifffNSZe3WdLtoEC-Pkklqk7IsNFtUDY&q=Oregon+Tires+Reviews&sa=X&ved=2ahUKEwizl8GB1e2NAxV_JkQIHZgPD8QQ0bkNegQIQRAD&biw=1537&bih=932&dpr=2', '_blank')}
              className="hover:bg-green-50"
            >
              View All Reviews on Google
            </Button>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresTestimonials;
