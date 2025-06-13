
import React from 'react';
import { Star } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

interface TestimonialsProps {
  primaryColor: string;
}

const OregonTiresTestimonials: React.FC<TestimonialsProps> = ({ primaryColor }) => {
  return (
    <section className="py-16 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>What Our Customers Say</h2>
          <p className="text-xl text-gray-600">Don't just take our word for it. Here's what real customers are saying about their experience with Oregon Tires.</p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          <Card className="bg-white">
            <CardHeader>
              <div className="flex items-center gap-2 mb-2">
                <div className="flex">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                  ))}
                </div>
                <Badge variant="secondary">Verified</Badge>
              </div>
              <CardTitle className="text-lg">Sarah Johnson</CardTitle>
              <p className="text-sm text-gray-500">2 weeks ago</p>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">
                Excellent service! They were able to fit me in same day for a tire repair. The staff was friendly and professional, and the price was very reasonable. Will definitely be back!
              </p>
            </CardContent>
          </Card>

          <Card className="bg-white">
            <CardHeader>
              <div className="flex items-center gap-2 mb-2">
                <div className="flex">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                  ))}
                </div>
                <Badge variant="secondary">Verified</Badge>
              </div>
              <CardTitle className="text-lg">Mike Rodriguez</CardTitle>
              <p className="text-sm text-gray-500">1 month ago</p>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">
                Oregon Tires has been my go-to shop for years. They always provide honest assessments and quality work. Recently had all four tires replaced and couldn't be happier with the service.
              </p>
            </CardContent>
          </Card>

          <Card className="bg-white">
            <CardHeader>
              <div className="flex items-center gap-2 mb-2">
                <div className="flex">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                  ))}
                </div>
                <Badge variant="secondary">Verified</Badge>
              </div>
              <CardTitle className="text-lg">Jennifer Chen</CardTitle>
              <p className="text-sm text-gray-500">3 weeks ago</p>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">
                Great experience from start to finish. They explained everything clearly, completed the work quickly, and the pricing was fair. Highly recommend for anyone needing tire service in Portland!
              </p>
            </CardContent>
          </Card>

          <Card className="bg-white">
            <CardHeader>
              <div className="flex items-center gap-2 mb-2">
                <div className="flex">
                  {[...Array(4)].map((_, i) => (
                    <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                  ))}
                  <Star className="h-4 w-4 text-gray-300" />
                </div>
                <Badge variant="secondary">Verified</Badge>
              </div>
              <CardTitle className="text-lg">David Thompson</CardTitle>
              <p className="text-sm text-gray-500">1 week ago</p>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">
                Fast and reliable service. Had a flat tire and they patched it up perfectly. The waiting area was clean and comfortable. Only minor complaint was the wait time, but understandable given how busy they are.
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="text-center">
          <div className="inline-block bg-white p-6 rounded-lg shadow-lg">
            <div className="text-4xl font-bold mb-2" style={{ color: primaryColor }}>4.8</div>
            <div className="text-gray-600 mb-2">out of 5</div>
            <div className="flex justify-center mb-2">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="h-5 w-5 fill-current text-yellow-400" />
              ))}
            </div>
            <div className="text-gray-600 mb-4">Based on 150+ Google Reviews</div>
            <Button 
              variant="outline" 
              style={{ borderColor: primaryColor, color: primaryColor }}
              onClick={() => window.open('https://www.google.com/search?sca_esv=6df4d1ed451ac289&sxsrf=AE3TifMy55UssDOtrXR8Esz2eSH5UOyS1g:1749792496572&si=AMgyJEtREmoPL4P1I5IDCfuA8gybfVI2d5Uj7QMwYCZHKDZ-E5EWQrHl7sppkcD-zb5r0m0iiLtgu2v1wQWQGknmEKiRI73YX7qCtCCI7-B3ifffNSZe3WdLtoEC-Pkklqk7IsNFtUDY&q=Oregon+Tires+Reviews&sa=X&ved=2ahUKEwizl8GB1e2NAxV_JkQIHZgPD8QQ0bkNegQIQRAD&biw=1537&bih=932&dpr=2', '_blank')}
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
