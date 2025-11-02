<?php

namespace App\Modules\Investment\Enums;

/**
 * Investment types enum
 */
enum InvestmentTypes: string
{
    case RealEstate = 'real-estate';
    case Forex = 'forex';
    case Crypto = 'crypto';
    case Commodities = 'commodities';
    case Bonds = 'bonds';
    case Metaverse = 'metaverse';
    case Mining = 'mining';
    case Fund = 'fund';
    case Nft = 'nft';
    case Agriculture = 'agriculture';
    case OilGas = 'oil-gas';

    /**
     * Get the label for the investment type
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::RealEstate => 'Real Estate',
            self::Forex => 'Forex',
            self::Crypto => 'Crypto',
            self::Commodities => 'Commodities',
            self::Bonds => 'Bonds',
            self::Metaverse => 'Metaverse',
            self::Mining => 'Mining',
            self::Fund => 'Fund',
            self::Nft => 'NFT',
            self::Agriculture => 'Agriculture',
            self::OilGas => 'Oil & Gas',
        };
    }

}
