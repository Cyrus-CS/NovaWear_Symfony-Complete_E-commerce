<?php

namespace App\Command;

use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:coupons:purge-expired',
    description: 'Supprime de la base de données tous les coupons dont la date d\'expiration est dépassée.',
)]
class PurgeExpiredCouponsCommand extends Command
{
    public function __construct(
        private readonly CouponRepository  $couponRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Simule la suppression sans écrire en base (pratique pour tester).',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $now    = new \DateTimeImmutable();

        $io->title('Purge des coupons expirés');

        // Récupère tous les coupons dont expiresAt est défini et antérieur à maintenant
        $expired = $this->couponRepository->findExpiredBefore($now);

        if (empty($expired)) {
            $io->success('Aucun coupon expiré trouvé.');
            return Command::SUCCESS;
        }

        $io->table(
            ['ID', 'Code', 'Expiré le'],
            array_map(
                fn ($c) => [
                    $c->getId(),
                    $c->getCode(),
                    $c->getExpiresAt()?->format('d/m/Y H:i'),
                ],
                $expired,
            ),
        );

        $count = count($expired);

        if ($dryRun) {
            $io->warning(sprintf('[DRY-RUN] %d coupon(s) seraient supprimés — aucune écriture effectuée.', $count));
            return Command::SUCCESS;
        }

        foreach ($expired as $coupon) {
            // Détacher le coupon de tous les paniers qui le référencent
            // pour éviter une violation de contrainte FK
            foreach ($coupon->getCarts() as $cart) {
                $cart->setCoupon(null);
                $cart->setDiscountAmount(null);
            }

            $this->em->remove($coupon);
        }

        $this->em->flush();

        $io->success(sprintf('%d coupon(s) expiré(s) supprimé(s) avec succès.', $count));

        return Command::SUCCESS;
    }
}