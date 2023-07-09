<?php

namespace white\commerce\sendcloud\models;

final class Address implements \Stringable
{
    public function __construct(
        private string $name,
        private ?string $companyName,
        private string $street,
        private string $city,
        private string $postalCode,
        private string $countryCode,
        private string $emailAddress,
        private ?string $houseNumber = null,
        private ?string $phoneNumber = null,
        private ?string $addressLine2 = null,
        private ?string $countryStateCode = null,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return void
     */
    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return void
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return null|string
     */
    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    /**
     * @param ?string $houseNumber
     * @return void
     */
    public function setHouseNumber(?string $houseNumber = null): void
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return void
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param null|string $phoneNumber
     * @return void
     */
    public function setPhoneNumber(?string $phoneNumber = null): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @retrun null|string
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @param null|string $addressLine2
     * @Return void
     */
    public function setAddressLine2(?string $addressLine2 = null): void
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * @return null|string
     */
    public function getCountryStateCode(): ?string
    {
        return $this->countryStateCode;
    }

    /**
     * @param null|string $countryStateCode
     * @return void
     */
    public function setCountryStateCode(?string $countryStateCode): void
    {
        $this->countryStateCode = $countryStateCode;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        $displayName = $this->getName();

        if ($this->getCompanyName()) {
            $displayName .= ' / ' . $this->getCompanyName();
        }

        return $displayName;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'city' => $this->getCity(),
            'companyName' => $this->getCompanyName(),
            'countryCode' => $this->getCountryCode(),
            'displayName' => $this->getDisplayName(),
            'emailAddress' => $this->getEmailAddress(),
            'houseNumber' => $this->getHouseNumber(),
            'name' => $this->getName(),
            'phoneNumber' => $this->getPhoneNumber(),
            'postalCode' => $this->getPostalCode(),
            'street' => $this->getStreet(),
            'addressLine2' => $this->getAddressLine2(),
            'countryStateCode' => $this->getCountryStateCode(),
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
