<?php

namespace WebsiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use WebsiteBundle\Entity\Partner;
use WebsiteBundle\Form\PartnerType;


class PartnerController extends Controller
{

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $partner = new Partner();
        $form = $this->createForm('WebsiteBundle\Form\PartnerType', $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($partner);
            $em->flush();

            return $this->redirectToRoute('website_admin_partners_main', array());
        }

        return $this->render('AdminBundle:Sections/Partner:create.html.twig', [
            'partner' => $partner,
            'form' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $partners = $em->getRepository('WebsiteBundle:Partner')->findAll();

        return $this->render('AdminBundle:Sections/Partner:index.html.twig',[
            'partners' => $partners
        ]);
    }


    /**
     * @param Partner $partner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Partner $partner)
    {
        $deleteForm = $this->createDeleteForm($partner);

        return $this->render('AdminBundle:Sections/Partner:show.html.twig',[
            'partner' => $partner,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Partner $partner)
    {
        $deleteForm = $this->createDeleteForm($partner);
        $editForm = $this->createForm('WebsiteBundle\Form\PartnerType', $partner);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($partner);
            $em->flush();

            $this->addFlash(
                'notice',
                'Modificările au fost salvate!'
            );

            return $this->redirectToRoute('website_admin_partners_edit', array('id' => $partner->getId()));
        }

        return $this->render('AdminBundle:Sections/Partner:edit.html.twig',[
            'partner' => $partner,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a Partner entity.
     */
    public function deleteAction(Request $request, Partner $partner)
    {
        $form = $this->createDeleteForm($partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($partner);
            $em->flush();
        }

        return $this->redirectToRoute('website_admin_partners_main');
    }

    /**
     * Creates a form to delete a Partner entity.
     * @param Partner $partner The Partner entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Partner $partner)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('website_admin_partners_delete', array('id' => $partner->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
