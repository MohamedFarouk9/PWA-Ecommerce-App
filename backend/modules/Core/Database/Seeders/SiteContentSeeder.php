<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\SiteContent;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            'about' => [
                'content' => "
    <p>Hi! I'm Mohamed Farouk. I am a dedicated software engineer with expertise in React, PHP, Node.js, and full-stack development.</p>

    <p>My focus is on creating efficient, scalable, and robust software solutions, seamlessly integrating both frontend and backend frameworks. I have a strong passion for building intuitive user interfaces and optimizing performance to enhance user experiences. With a background in containerization and microservices,</p>

    <p>I aim to develop modular and resilient applications that can scale effortlessly. I thrive on tackling complex challenges, always striving to stay up-to-date with the latest industry trends and tools.</p>

    <p>My goal is to deliver high-quality, impactful software that drives innovation and meets business needs. Whether working independently or as part of a collaborative team, I am committed to achieving excellence in every project.</p>",
            ],
            'refund' => [
                'content' => "<h1>Refund Policy</h1>

    <p>We strive to ensure that our customers are completely satisfied with their purchases. If you are not entirely happy with your purchase, we're here to help.</p>

    <h2>Returns</h2>
    <p>You have <strong>30 days</strong> from the date of purchase to return an item for a full refund. To be eligible for a return, your item must be unused and in the same condition that you received it. The item must also be in the original packaging.</p>

    <h2>Refunds</h2>
    <p>Once we receive your returned item, we will inspect it and notify you of the status of your refund. If your return is approved, we will initiate a refund to your original method of payment. You will receive the credit within a certain number of days, depending on your card issuer's policies.</p>

    <h2>Shipping</h2>
    <p>You will be responsible for paying your own shipping costs for returning your item. Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted from your refund.</p>

    <h2>Non-Refundable Items</h2>
    <p>Certain items are non-refundable, including but not limited to:
        <ul>
            <li>Gift cards</li>
            <li>Downloaded software products</li>
            <li>Personalized items</li>
        </ul>
    </p>

    <h2>Exchanges</h2>
    <p>If you received a defective or damaged item, we will gladly replace it with a new one. Please contact us with the details of the product and proof of damage to initiate the exchange process.</p>

    <h2>Contact Us</h2>
    <p>If you have any questions about our refund policy, please contact us at [your email address] or through our customer service page.</p>",
            ],
            'privacy' => [
                'content' => "
    <p>Your privacy is important to us. It is our policy to respect your privacy regarding any information we may collect from you across our website and other sites we own and operate.</p>

    <h2>Information We Collect</h2>
    <p>We may collect both personal and non-personal information about you. Personal information may include your name, email address, and other contact details, while non-personal information may include data about your usage of our website, such as browser type, IP address, and pages visited.</p>

    <h2>How We Use Information</h2>
    <p>We use the information we collect to provide, operate, and improve our services. We may also use your information to communicate with you, send you updates, respond to your inquiries, and for marketing and promotional purposes.</p>

    <h2>Sharing Your Information</h2>
    <p>We do not share your personal information with third parties except when necessary to provide our services, comply with the law, or protect our rights. We may share aggregated or non-personally identifiable information with partners or advertisers for research or analysis.</p>

    <h2>Data Security</h2>
    <p>We take data security seriously and use industry-standard measures to protect your information from unauthorized access, disclosure, alteration, or destruction. However, no data transmission over the internet can be guaranteed to be 100% secure, and we cannot ensure the absolute security of any information you transmit to us.</p>

    <h2>Your Rights</h2>
    <p>You have the right to access, update, or delete the personal information we have about you. If you would like to exercise these rights, please contact us directly, and we will assist you with your request.</p>

    <h2>Changes to This Policy</h2>
    <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page. You are advised to review this page periodically for any updates.</p>

    <h2>Contact Us</h2>
    <p>If you have any questions about our Privacy Policy or your data, please contact us at [your email address] or through the contact form on our website.</p>",
            ],
            'purchase_guide' => [
                'content' => "
    <p>Welcome to our Purchase Guide! This guide will walk you through the steps of making a purchase on our website, from selecting a product to completing your order.</p>

    <h2>Step 1: Browse Products</h2>
    <p>Explore our collection of products by visiting the <strong>Shop</strong> section on our website. You can filter products by categories, price, or popularity to find exactly what you're looking for.</p>

    <h2>Step 2: Product Details</h2>
    <p>Click on any product to view its details. You will find information about the product's features, specifications, pricing, and customer reviews. Make sure to review all details to ensure the product meets your needs.</p>

    <h2>Step 3: Add to Cart</h2>
    <p>Once you have found the product you wish to purchase, click the <strong>Add to Cart</strong> button. You can continue shopping and add more items to your cart or proceed directly to checkout.</p>

    <h2>Step 4: Review Your Cart</h2>
    <p>Go to your cart to review the items you have selected. You can update the quantity of any product or remove items if necessary. Ensure that everything in your cart is correct before proceeding.</p>

    <h2>Step 5: Checkout</h2>
    <p>When you are ready to complete your purchase, click on the <strong>Checkout</strong> button. You will be prompted to enter your shipping information, billing address, and payment details. We accept a variety of payment methods to make your shopping experience convenient.</p>

    <h2>Step 6: Order Confirmation</h2>
    <p>After entering your payment details, review your order summary, and click on the <strong>Place Order</strong> button. You will receive an order confirmation email with the details of your purchase and a tracking number if applicable.</p>

    <h2>Step 7: Shipping and Delivery</h2>
    <p>Your order will be processed and shipped according to the delivery method you selected. You can track your order using the tracking number provided in the confirmation email.</p>

    <h2>Customer Support</h2>
    <p>If you have any questions or need assistance during the purchasing process, please contact our customer support team at [your email address] or through our contact page.</p>

    <p>Thank you for shopping with us! We hope you have a great experience.</p>",
            ],
        ];

        foreach ($contents as $type => $data) {
            SiteContent::firstOrCreate(
                ['type' => $type],
                [
                    'content' => $data['content'],
                    'version' => Str::random(10),
                ]
            );
        }
    }
}
