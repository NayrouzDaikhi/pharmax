<?php

namespace App\Controller;

use App\Service\ReportingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reports')]
#[IsGranted('ROLE_ADMIN')]
class ReportingController extends AbstractController
{
    public function __construct(
        private ReportingService $reportingService,
    ) {
    }

    /**
     * Main reporting dashboard
     */
    #[Route('', name: 'admin_reports_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $stats = $this->reportingService->getDashboardStats();
        $customerStats = $this->reportingService->getCustomerStats();
        $topProducts = $this->reportingService->getTopProducts();

        return $this->render('admin/reports/dashboard.html.twig', [
            'stats' => $stats,
            'customerStats' => $customerStats,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Period report (custom date range)
     */
    #[Route('/period', name: 'admin_reports_period', methods: ['GET', 'POST'])]
    public function periodReport(Request $request): Response
    {
        $startDate = null;
        $endDate = null;
        $report = null;

        if ($request->isMethod('POST')) {
            $startDateStr = $request->request->get('startDate');
            $endDateStr = $request->request->get('endDate');

            if ($startDateStr && $endDateStr) {
                try {
                    $startDate = \DateTime::createFromFormat('Y-m-d', $startDateStr);
                    $endDate = \DateTime::createFromFormat('Y-m-d', $endDateStr);
                    $endDate->setTime(23, 59, 59);

                    if ($startDate && $endDate) {
                        $report = $this->reportingService->generatePeriodReport($startDate, $endDate);
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Invalid date format');
                }
            }
        }

        return $this->render('admin/reports/period.html.twig', [
            'report' => $report,
            'startDate' => $startDate?->format('Y-m-d'),
            'endDate' => $endDate?->format('Y-m-d'),
        ]);
    }

    /**
     * Trend analysis
     */
    #[Route('/trends', name: 'admin_reports_trends', methods: ['GET'])]
    public function trends(Request $request): Response
    {
        $days = min((int) $request->query->get('days', 30), 365);
        $trends = $this->reportingService->getTrends();

        // Generate chart data
        $orderTrend = $this->reportingService->getOrdersTrend($days);
        $complaintTrend = $this->reportingService->getComplaintsTrend($days);

        return $this->render('admin/reports/trends.html.twig', [
            'orderTrend' => $orderTrend,
            'complaintTrend' => $complaintTrend,
            'days' => $days,
        ]);
    }

    /**
     * API endpoint: Get dashboard stats as JSON
     */
    #[Route('/api/stats', name: 'api_reports_stats', methods: ['GET'])]
    public function apiStats(): JsonResponse
    {
        $stats = $this->reportingService->getDashboardStats();

        return new JsonResponse($stats);
    }

    /**
     * API endpoint: Get period report as JSON
     */
    #[Route('/api/period', name: 'api_reports_period', methods: ['GET'])]
    public function apiPeriod(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        if (!$startDateStr || !$endDateStr) {
            return new JsonResponse(['error' => 'Missing date parameters'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $startDate = \DateTime::createFromFormat('Y-m-d', $startDateStr);
            $endDate = \DateTime::createFromFormat('Y-m-d', $endDateStr);
            $endDate->setTime(23, 59, 59);

            if (!$startDate || !$endDate) {
                throw new \Exception('Invalid date format');
            }

            $report = $this->reportingService->generatePeriodReport($startDate, $endDate);

            return new JsonResponse($report);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * API endpoint: Get trends as JSON
     */
    #[Route('/api/trends', name: 'api_reports_trends', methods: ['GET'])]
    public function apiTrends(Request $request): JsonResponse
    {
        $days = min((int) $request->query->get('days', 30), 365);

        $trends = [
            'orders' => $this->reportingService->getOrdersTrend($days),
            'complaints' => $this->reportingService->getComplaintsTrend($days),
        ];

        return new JsonResponse($trends);
    }

    /**
     * API endpoint: Get top products as JSON
     */
    #[Route('/api/top-products', name: 'api_reports_top_products', methods: ['GET'])]
    public function apiTopProducts(Request $request): JsonResponse
    {
        $limit = min((int) $request->query->get('limit', 10), 100);
        $products = $this->reportingService->getTopProducts($limit);

        return new JsonResponse($products);
    }

    /**
     * API endpoint: Get customer statistics as JSON
     */
    #[Route('/api/customers', name: 'api_reports_customers', methods: ['GET'])]
    public function apiCustomers(): JsonResponse
    {
        $stats = $this->reportingService->getCustomerStats();

        return new JsonResponse($stats);
    }
}
